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

/** Process settings
 * Example
 * 
 */
$settings = array(
  'chunksize' => 1000,
);


