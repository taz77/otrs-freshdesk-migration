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
 * chunksize is the maximum number of calls you want to perform per run of the
 * script. Freshdesk is limited to 1000 calls per hour.
 *
 * nulltitle should be set to whatever you want the subject of the ticket to be
 * if there is no subject
 *
 * nullsender should be set to whatever you want the email address to be for a
 * ticket that may not have an email address
 *
 * fdeskurl is the URL of your Freshdesk account with protocol.
 * Recommend using SSL.
 *
 * fdeskapikey is your API key.  See here on how to obtain:
 * http://freshdesk.com/api#authentication
 * Example
 * $settings = array(
 *  'chunksize' => 5,
 *  'csemailaddr' => 'support@example.com',
 *  'nulltitle' => 'No Title',
 *  'nullsender' => 'noreply@example.com',
 *  'fdeskurl' => 'example.freshdesk.com',
 *  'fdeskapikey' => 'asdfas897sa9d8798asd',
 * );
 */
$settings = array(
  'chunksize' => 1000,
  'csemailaddr' => '',
  'nulltitle' => '',
  'nullsender' => '',
  'fdeskurl' => '',
  'fdeskapikey' => '',
);


