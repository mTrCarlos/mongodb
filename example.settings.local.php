<?php
/**
 * @file
 * Example settings to connect to MongoDB.
 *
 * This is the default data to add to your settings.local.php.
 */

$settings['mongodb_connections'] = [
  'servers' => [
    'default' => [
      'host' => 'localhost',
      'port' => 27017,
      'db' => 'drupal',
    ],
    // A DB for volatile data to clear them with a single dropDatabase().
    'volatile' => [
      'host' => 'localhost',
      'port' => 27017,
      'db' => 'volatile',
    ],
  ],
  'collections' => [
    'cache' => 'volatile',
  ],
];