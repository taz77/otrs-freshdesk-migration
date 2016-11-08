<?php

/**
 * @file Connection test file.
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


// Process base tickets.
$header[] = 'Content-type: application/json';
$connection = curl_init('https://' . $settings['fdeskurl'] . '/api/v2/settings/helpdesk');
curl_setopt($connection, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($connection, CURLOPT_HTTPHEADER, $header);
curl_setopt($connection, CURLOPT_HEADER, FALSE);
curl_setopt($connection, CURLOPT_USERPWD, $settings['fdeskapikey'] . ':X');

try {
  $response = curl_exec($connection);
  //print_r($response);
  if (curl_getinfo($connection, CURLINFO_HTTP_CODE) == '403') {
    die(PHP_EOL . 'You have hit your hourly API call limit. You processed a total of ' . $z . ' base tickets. Run one hour from now.' . PHP_EOL);
  }
  $respondedecoded = json_decode($response, TRUE);
}
catch (Exception $e) {
  die('Error Thrown ' . $e);
}
// We only update if curl was successful.
if (curl_getinfo($connection, CURLINFO_HTTP_CODE) == 200) {
  print_r("\n" . 'Connection was successful with the supplied credentials' . "\n");
  curl_close($connection);
  exit;
}

// We only update if curl was successful.
if (curl_getinfo($connection, CURLINFO_HTTP_CODE) == 401) {
  print_r("\n" . 'Connection was NOT established with the supplied credentials' . "\n");
  curl_close($connection);
}

print_r("\n" . 'Nothing could be determined based on the information provided. No connection was made.');
curl_close($connection);
