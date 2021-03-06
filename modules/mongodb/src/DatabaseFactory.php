<?php
namespace Drupal\mongodb;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Site\Settings;

/**
 * Class DatabaseFactory.
 *
 * @package Drupal\mongodb
 */
class DatabaseFactory {

  /**
   * The Client factory service.
   *
   * @var \Drupal\mongodb\ClientFactory
   */
  protected $clientFactory;

  /**
   * The 'mongodb' database settings array.
   *
   * @var string[][]
   */
  protected $settings;

  /**
   * Constructor.
   *
   * @param \Drupal\mongodb\ClientFactory $client_factory
   *   The Client factory service.
   */
  public function __construct(ClientFactory $client_factory, Settings $settings) {
    $this->clientFactory = $client_factory;
    $this->settings = $settings->get('mongodb')['databases'];
  }

  /**
   * Return the MongoDB database matching an alias.
   *
   * @param string $alias
   *   The alias string, like "default".
   *
   * @return \MongoDB\Database|null
   *   The selected database, or NULL if an error occurred.
   */
  public function get($alias) {
    if (!isset($this->settings[$alias])) {
      throw new \InvalidArgumentException(new FormattableMarkup('Nonexistent database alias: @alias', [
        '@alias' => $alias,
      ]));
    }
    try {
      list($client_alias, $database) = $this->settings[$alias];
      $client = $this->clientFactory->get($client_alias);
      $result = $client->selectDatabase($database);
    }
    // Includes its descendant \MongoDb\Exception\InvalidArgumentException.
    catch (\InvalidArgumentException $e) {
      $result = NULL;
    }

    return $result;
  }

}
