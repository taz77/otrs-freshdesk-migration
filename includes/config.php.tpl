<?php

/**
 * @file Configuration file
 * 
 * Variables to configure
 * database (required) name of schema
 * username (required) database user
 * password (required) database password if set - can be empty value
 * host (required) database host
 * port (required) database host port if needed - can be empty value
 * prefix (required) database prefix if any - can be empty value
 * driver (required) type of database must be either mysql or pgsql
 * 
 * Example database configuration.
 * 
 * $databases = array(
 * 'default' =>
 *   array(
 *     'default' =>
 *      array(
 *        'database' => 'otrs',
 *        'username' => 'root',
 *        'password' => 'pass',
 *        'host' => 'localhost',
 *        'port' => '',
 *        'prefix' => '',
 *        'driver' => 'mysql',
 *      ),
 *    ),
 * );
 * 
 */

// OTRS Database Settings
$databases = array(
  'default' =>
  array(
    'default' =>
    array(
      'database' => '',
      'username' => '',
      'password' => '',
      'host' => '',
      'port' => '',
      'prefix' => '',
      'driver' => '',
    ),
  ),
);

/**
 * Process settings.
 * 
 * If you have a customer support email address that was used in HTML forms
 * that sent emails to OTRS.  Supply that email address in csemailaddr so that
 * it does not get used when the tickets are created and we use a reply-to
 * field instead.
 * 
 * nulltitle should be set to whatever you want the subject of the ticket to be
 * if there is no subject
 * 
 * nullsender should be set to whatever you want the email address to be for a 
 * ticket that may not have an email address
 * 
 * Example
 * 
 */
$settings = array(
  'chunksize' => 1000,
);


