<?php

/**
 * @file Main execute file.
 */
global $databases;
// Require the configuration file.
require_once(dirname(__FILE__) . '/includes/config.php');
// Load the functions file.
require_once(dirname(__FILE__) . '/includes/functions.php');
// Load text functions needed for DB layer.
require_once(dirname(__FILE__) . '/includes/unicode.inc');
// Load the database include file and also load the driver file set in the config
require_once(dirname(__FILE__) . '/includes/database/database.inc');
require_once dirname(__FILE__) . '/includes/database/' . $databases['default']['default']['driver'] . '/database.inc';

// Create an Freshdesk updated or not field for cron processing.
$spec1 = array(
  'description' => 'Processed ticket to Freshdesk.',
  'type' => 'int',
  'size' => 'normal',
  'default' => 0,
);
// Create an Freshdesk ticket ID field.
$spec2 = array(
  'description' => 'Freshdesk ID of ticket.',
  'type' => 'int',
  'size' => 'normal',
  'default' => 0,
);

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
$query = 'SELECT id, title FROM {ticket} WHERE freshdesk_updated = 0 ORDER by id LIMIT ' . $settings['chunksize'];
$result = db_query($query);
if ($result->rowCount() != 0) {
  // Process base tickets.
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

    // Set table field to indicate completed ticket.
    /*
      db_update('ticket')
      ->fields(array(
      'freshdesk_updated' => 1,
      ))
      ->condition('id', $item->id)
      ->execute(); */
  }
}
elseif ($result->rowCount() == 0) {
  // Process ticket replies and notes.
}
?>