<?php

/**
 * @file Main execute file.
 */

// Composer.
require 'vendor/autoload.php';

global $databases;
// Require the configuration file.
require_once(dirname(__FILE__) . '/includes/config.php');
// Set default timezone.
date_default_timezone_set('UTC');

// Setup logging.
if (!empty($settings['logfilepath'])) {
  try {
    $logger = new Katzgrau\KLogger\Logger($settings['logfilepath']);
  }
  catch (Exception $e) {
    print_r('Error has occurred: ' . $e . "\n" . 'Exiting');
    exit;
  }
}
else {
  try {
    $logger = new Katzgrau\KLogger\Logger(__DIR__ . '/logs');
  }
  catch (Exception $e) {
    print_r('Error has occurred: ' . $e . "\n" . 'Exiting');
    exit;
  }
}

// Successful HTTP codes.
$successcodes = [
  200,
  201,
  202,
  204,
];

// Load the functions file.
require_once(dirname(__FILE__) . '/includes/functions.php');
// Load text functions needed for DB layer.
require_once(dirname(__FILE__) . '/includes/unicode.inc');
// Load the database include file and also load the driver file set in the config
require_once(dirname(__FILE__) . '/includes/database/database.inc');
require_once dirname(__FILE__) . '/includes/database/' . $databases['default']['default']['driver'] . '/database.inc';
// Create an empty message variable.
$message = '';
$message .= PHP_EOL;
// Create an Freshdesk updated or not field for cron processing.
$spec1 = [
  'description' => 'Processed ticket to Freshdesk.',
  'type' => 'int',
  'size' => 'normal',
  'default' => 0,
];
// Create an Freshdesk ticket ID field.
$spec2 = [
  'description' => 'Freshdesk ID of ticket.',
  'type' => 'int',
  'size' => 'normal',
  'default' => 0,
];

// Add fields to the tables needed.
if (!db_field_exists('ticket', 'freshdesk_updated')) {
  db_add_field('ticket', 'freshdesk_updated', $spec1);
}
// Add fields to the tables needed.
if (!db_field_exists('ticket', 'freshdesk_id')) {
  db_add_field('ticket', 'freshdesk_id', $spec2);
}
// Add fields to the tables needed.
if (!db_field_exists('ticket', 'freshdesk_updated_article')) {
  db_add_field('ticket', 'freshdesk_updated_article', $spec1);
}
// Add fields to the tables needed.
if (!db_field_exists('article', 'freshdesk_updated')) {
  db_add_field('article', 'freshdesk_updated', $spec1);
}

// First process all base tickets till they are exhausted
$i = 0;
$z = 0;
$query = 'SELECT id, tn, title FROM {ticket} WHERE freshdesk_updated = 0 ORDER by id LIMIT ' . $settings['chunksize'];
$result = db_query($query);
if ($result->rowCount() != 0) {
  // Process base tickets.
  $header[] = 'Content-type: application/json';
  $connection = curl_init('https://' . $settings['fdeskurl'] . '/api/v2/tickets');
  curl_setopt($connection, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($connection, CURLOPT_HTTPHEADER, $header);
  curl_setopt($connection, CURLOPT_HEADER, FALSE);
  curl_setopt($connection, CURLOPT_USERPWD, $settings['fdeskapikey'] . ':X');
  curl_setopt($connection, CURLOPT_POST, TRUE);
  foreach ($result as $item) {
    // We pull the first article for the ticket in order to get email addresses.
    $articleresult = db_query('SELECT id, a_body, a_from, a_reply_to FROM {article} WHERE ticket_id = ' . $item->id . ' ORDER BY id LIMIT 1');
    $record = $articleresult->fetchAssoc();
    // Several logic checks to set the right email address (sender).
    if ($record['a_from'] == $settings['csemailaddr'] && !empty($record['a_reply_to'])) {
      $sender = $record['a_reply_to'];
    }
    if (empty($record['a_from']) && empty($record['a_reply_to'])) {
      $sender = $settings['nullsender'];
    }
    if (empty($record['a_from']) && !empty($record['a_reply_to'])) {
      $sender = $record['a_reply_to'];
    }
    if (empty($sender) && !empty($record['a_from'])) {
      $sender = $record['a_from'];
    }

    preg_match('/[\\w\\.\\-+=*_]*@[\\w\\.\\-+=*_]*/', $sender, $regs);
    $parsedsender = $regs[0];

    // We are going to set all tickets to closed
    $status = 5;
    // We are going to set all ticket priorities to the lowerst value
    $priority = 1;
    // Build the ticket description which has the contents of the ticket
    $description = 'OTRS Ticket Number: ' . $item->tn . '<br/>' . "\n";
    $description .= 'Other Information: ' . '<br/>' . "\n";
    $description .= 'Reply To: ' . $record['a_reply_to'] . '<br/>' . "\n";
    $description .= 'From: ' . $record['a_from'] . '<br/>' . "\n";
    $description .= $record['a_body'];
    if (empty($item->title)) {
      $item->title = 'Empty Subject - No title';
    }
    $data = [
      'description' => $description,
      'subject' => $item->title,
      'email' => $parsedsender,
      'priority' => $priority,
      'status' => $status,
    ];

    $json_body = json_encode($data, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
    try {
      curl_setopt($connection, CURLOPT_POSTFIELDS, $json_body);
      $response = curl_exec($connection);
      if (curl_getinfo($connection, CURLINFO_HTTP_CODE) == '403') {
        $logger->error('You have hit your hourly API call limit.');
        die(PHP_EOL . 'You have hit your hourly API call limit. You processed a total of ' . $z . ' base tickets. Run one hour from now.' . PHP_EOL);
      }
      $respondedecoded = json_decode($response, TRUE);
      if (!empty($respondedecoded['description']) && $respondedecoded['description'] == 'Validation failed') {
        print_r("\n" . $response . "\n");
        print_r("\n" . $respondedecoded['description'] . "\n");
        $logger->error('A field is invalid. Response: ' . $response);
        throw new Exception('A field is invalid');
      }
    }
    catch (Exception $e) {
      die('Error Thrown ' . $e);
    }
    if ($debug == TRUE) {
      $logger->debug('Ticker OTR ID Number: ' . $item->id);
      $logger->debug('HTTP Response Code from base ticket call: ' . curl_getinfo($connection, CURLINFO_HTTP_CODE));
      $logger->debug('Data sent to Freshdesk: ' . serialize($data));
      $logger->debug('Response from Freshdesk: ' . $response);
    }

    // Hault processing if a 400 code was received
    if (curl_getinfo($connection, CURLINFO_HTTP_CODE) >= 400 && curl_getinfo($connection, CURLINFO_HTTP_CODE) <= 500) {
      $errormessage = "\n" . 'Error code received: ' . curl_getinfo($connection, CURLINFO_HTTP_CODE) . "\n";
      $errormessage .= "\n" . 'Data sent to Freshdesk' . "\n";
      $errormessage .= $data;
      $errormessage .= "\n" . 'Response from Freshdesk' . "\n";
      $errormessage .= $response;
      $logger->error($errormessage);
      throw new Exception('Error Response received from Freshdesk. Check your information');
      exit;
    }

    // We only update if curl was successful.
    if (in_array(curl_getinfo($connection, CURLINFO_HTTP_CODE), $successcodes)) {
      $z++;
      $ticketid = $respondedecoded['helpdesk_ticket']['display_id'];
      $logger->info('Created Freshdesk Ticket ' . $ticketid . '. Sender ' . $sender);
      // Set table field to indicate completed ticket.
      db_update('ticket')
        ->fields([
          'freshdesk_updated' => 1,
          'freshdesk_id' => $ticketid,
        ])
        ->condition('id', $item->id)
        ->execute();
      // Must also mark the first article as done so we don't make dupes later.
      db_update('article')
        ->fields([
          'freshdesk_updated' => 1,
        ])
        ->condition('id', $record['id'])
        ->execute();
    }
    unset($sender);
    unset($description);
  }
  $message .= 'Processed ' . $result->rowCount() . ' base tickets.' . PHP_EOL;
}
elseif ($result->rowCount() == 0) {
  // Process ticket replies and notes.
  $query = 'SELECT id, freshdesk_id FROM {ticket} WHERE freshdesk_updated_article = 0 AND freshdesk_id !=0 ORDER by id';
  $ticketresult = db_query($query);
  if ($ticketresult->rowCount() == 0) {
    die('Processing is over out of base tickets to process' . PHP_EOL);
  }

  foreach ($ticketresult as $item) {
    if ($i >= $settings['chunksize']) {
      $message .= 'Process limit hit. Ran ' . $i . ' iterations processing articles.' . PHP_EOL;
      $message .= 'Total process size was set to ' . $settings['chunksize'] . PHP_EOL;
      break;
    }
    $articleresult = db_query('SELECT id, a_body, a_from, a_reply_to FROM {article} WHERE ticket_id = ' . $item->id . ' AND freshdesk_updated = 0  ORDER BY id');
    $articlecount = $articleresult->rowCount();
    if ($debug == TRUE) {
      $logger->debug('Number of articles ' . $articlecount);
    }
    if ($articlecount == 0) {
      // No more articles mark the ticket as done.
      db_update('ticket')
        ->fields([
          'freshdesk_updated_article' => 1,
        ])
        ->condition('id', $item->id)
        ->execute();
    }
    else {
      $j = 1;
      foreach ($articleresult as $notes) {
        if ($debug == TRUE) {
          $logger->debug('API ITERATION i: ' . $i . ' of ' . $settings['chunksize'] . 'total');
          $logger->debug('ARTICLE ITERATION NUMBER ' . $j);
        }
        if ($i >= $settings['chunksize']) {
          $message .= 'Process limit hit. Ran ' . $i . ' iterations processing articles.' . PHP_EOL;
          $message .= 'Total process size was set to ' . $settings['chunksize'] . PHP_EOL;
          break;
        }
        $data = [
          'body' => $notes->a_body,
          'private' => FALSE,
        ];

        $json_body = json_encode($data, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);

        $header[] = 'Content-type: application/json';
        try {
          $connection = curl_init('https://' . $settings['fdeskurl'] . '/api/v2/tickets/' . $item->freshdesk_id . '/notes');
          curl_setopt($connection, CURLOPT_RETURNTRANSFER, TRUE);
          curl_setopt($connection, CURLOPT_HTTPHEADER, $header);
          curl_setopt($connection, CURLOPT_HEADER, FALSE);
          curl_setopt($connection, CURLOPT_USERPWD, $settings['fdeskapikey'] . ':X');
          curl_setopt($connection, CURLOPT_POST, TRUE);
          curl_setopt($connection, CURLOPT_POSTFIELDS, $json_body);

          $response = curl_exec($connection);
          if (curl_getinfo($connection, CURLINFO_HTTP_CODE) == '403') {
            $logger->error('You have hit your hourly API call limit.');
            die(PHP_EOL . 'You have hit your hourly API call limit. You processed ' . $i . ' articles. Run one hour from now.' . PHP_EOL);
          }
          // Hault processing if a 400 code was received
          if (curl_getinfo($connection, CURLINFO_HTTP_CODE) >= 400 && curl_getinfo($connection, CURLINFO_HTTP_CODE) <= 500) {
            $errormessage = "\n" . 'Error code received: ' . curl_getinfo($connection, CURLINFO_HTTP_CODE) . "\n";
            $errormessage .= "\n" . 'Data sent to Freshdesk' . "\n";
            $errormessage .= $data;
            $errormessage .= "\n" . 'Response from Freshdesk' . "\n";
            $errormessage .= $response;
            $logger->error($errormessage);
            throw new Exception('Error Response received from Freshdesk. Check your information');
          }
          $respondedecoded = json_decode($response, TRUE);
        }
        catch (Exception $e) {
          die('Error Thrown ' . $e);
        }
        // We only update if curl was successful.
        if (in_array(curl_getinfo($connection, CURLINFO_HTTP_CODE), $successcodes)) {
          // Set table field to indicate completed ticket.
          db_update('article')
            ->fields([
              'freshdesk_updated' => 1,
            ])
            ->condition('id', $notes->id)
            ->execute();
        }
        $i++;
        $j++;
        if ($j > $articlecount) {
          // No more articles mark the ticket as done.
          db_update('ticket')
            ->fields([
              'freshdesk_updated_article' => 1,
            ])
            ->condition('id', $item->id)
            ->execute();
        }
      }
    }
  }
}
if ($i < $settings['chunksize'] && $result->rowCount() == 0) {
  $message .= 'Process limit was not hit.  Ran out of data to process. Ran ' . $i . ' iterations processing articles.' . PHP_EOL;
  $message .= 'Total process size was set to ' . $settings['chunksize'] . PHP_EOL;
}
if ($i == 0) {
  $message .= 'No articles was processed. Process limit hit during base ticket processing. Continue running to process articles.' . PHP_EOL;
  $message .= 'Total process size was set to ' . $settings['chunksize'] . PHP_EOL;
}
$logger->info($message);
curl_close($connection);
print_r($message);
