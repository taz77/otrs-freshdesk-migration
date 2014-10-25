<?php

/**
 * @file Configuration file
 * 
 * Set driver to either mysql or pgsql.
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


