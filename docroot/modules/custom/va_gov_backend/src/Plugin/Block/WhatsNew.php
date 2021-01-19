<?php

namespace Drupal\va_gov_backend\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides information about what has recently changed in the CMS.
 *
 * @Block(
 *   id = "va_gov_backend_whats_new",
 *   admin_label = @Translation("What's new in the CMS"),
 * )
 */
class WhatsNew extends BlockBase implements ContainerFactoryPluginInterface {

  // The NID of the announcments node.
  const ANNOUNCEMENT_NODE_ID = '6900';

  /**
   * The node storage so we can interact with the announcements node.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, NodeStorageInterface $node_storage, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $node_storage;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $see_latest = $this->t('See latest announcements');
    $last_updated = $this->t('Last updated');
    $node = $this->nodeStorage->load(static::ANNOUNCEMENT_NODE_ID);
    $last_revision_date = $this->dateFormatter->format($node->getChangedTime(), 'standard');
    $link_url = $node->toUrl()->toString();
    $markup = '<div><a href="' . $link_url . '">' . $see_latest . '</a></div><div>' . $last_updated . ' ' . $last_revision_date . '</div>';

    return [
      '#markup' => $markup,
      '#attached' => [
        'library' => ['vagovadmin/whatsnew'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'node_list',
      'node:' . static::ANNOUNCEMENT_NODE_ID,
    ]);
  }

}
