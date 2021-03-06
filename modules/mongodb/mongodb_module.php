<?php

/**
 * @file
 * Contains the main module connecting Drupal to MongoDB.
 */

use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Implements hook_help().
 */
function mongodb_help($route_name, RouteMatchInterface $route_match) {
  switch ($route_name) {
    case 'help.page.mongodb':
      return '<p>' . t('The Drupal <a href=":project">MongoDB</a> project implements a generic interface to the <a href=":mongo">MongoDB</a> database server. Read its <a href=":docs">online documentation</a>.', [
        ':project' => 'https://www.drupal.org/project/mongodb',
        ':mongo' => 'https://www.mongodb.com/',
        ':docs' => 'https://fgm.github.io/mongodb',
      ]);
  }
}

/* ==== Highly suspicious below this line =================================== */

/**
 * Return the next id in a sequence.
 *
 * @param string $name
 *   The name of the sequence.
 * @param int $existing_id
 *   An existing id.
 *
 * @return int
 *   The next id in the sequence.
 *
 * @throws \MongoConnectionException
 *   If the connection cannot be established.
 */
function mongodb_next_id($name, $existing_id = 0) {
  // Atomically get the next id in the sequence.
  $mongo = mongodb();
  $cmd = array(
    'findandmodify' => mongodb_collection_name('sequence'),
    'query' => array('_id' => $name),
    'update' => array('$inc' => array('value' => 1)),
    'new' => TRUE,
  );
  // It's very likely that this is not necessary as command returns an array
  // not an exception. The increment will, however, will fix the problem of
  // the sequence not existing. Still, better safe than sorry.
  try {
    $sequence = $mongo->command($cmd);
    $value = isset($sequence['value']['value']) ? $sequence['value']['value'] : 0;
  }
  catch (Exception $e) {
  }
  if (0 < $existing_id - $value + 1) {
    $cmd = array(
      'findandmodify' => mongodb_collection_name('sequence'),
      'query' => array('_id' => $name),
      'update' => array('$inc' => array('value' => $existing_id - $value + 1)),
      'upsert' => TRUE,
      'new' => TRUE,
    );
    $sequence = $mongo->command($cmd);
    $value = isset($sequence['value']['value']) ? $sequence['value']['value'] : 0;
  }
  return $value;
}

/**
 * Returns default options for MongoDB write operations.
 *
 * @param bool $safe
 *   Set it to FALSE for "fire and forget" write operation.
 *
 * @return array
 *   Default options for Mongo write operations.
 */
function mongodb_default_write_options($safe = TRUE) {
  if ($safe) {
    if (version_compare(phpversion('mongo'), '1.5.0') == -1) {
      return array('safe' => TRUE);
    }
    else {
      return variable_get('mongodb_write_safe_options', array('w' => 1));
    }
  }
  else {
    if (version_compare(phpversion('mongo'), '1.3.0') == -1) {
      return array();
    }
    else {
      return variable_get('mongodb_write_nonsafe_options', array('w' => 0));
    }
  }
}
