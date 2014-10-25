<?php

/**
 * @file Main execute file.
 */
global $databases;
// Require the configuration file.
require_once(dirname(__FILE__) . '/includes/config.php');
// Load the database include file and also load the driver file set in the config
require_once(dirname(__FILE__) . '/includes/database/database.inc');
require_once dirname(__FILE__) . '/includes/database/' . $databases['default']['default']['driver'] . '/database.inc';



// Create an Freshdesk updated or not field for cron processing.
$spec1 = array(
  'description' => 'Processed ticket to Freshdesk.',
  'type' => 'int',
  'size' => 'normal',
  'not null' => TRUE,
);
// Create an Freshdesk ticket ID field.
$spec2 = array(
  'description' => 'Freshdesk ID of ticket.',
  'type' => 'int',
  'size' => 'normal',
  'not null' => TRUE,
);

// Add fields to the tables needed.
if (!db_field_exists('tickets', 'freshdesk_updated')) {
  db_add_field('tickets', 'freshdesk_updated', $spec1);
}
// Add fields to the tables needed.
if (!db_field_exists('tickets', 'freshdesk_id')) {
  db_add_field('tickets', 'freshdesk_id', $spec2);
}
// Add fields to the tables needed.
if (!db_field_exists('article', 'freshdesk_updated')) {
  db_add_field('article', 'freshdesk_updated', $spec1);
}


