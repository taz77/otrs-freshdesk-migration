<?php

/**
 * @file How long will this take?
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

$result = db_query('SELECT * FROM {article}');
$article = $result->rowCount();

echo 'Running at a maximum of 5000 calls per hour' . PHP_EOL;
echo 'It will take ' . ceil($article / 5000)  . ' hours to complete your migration' . PHP_EOL;
