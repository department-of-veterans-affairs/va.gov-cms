<?php

namespace Drupal\va_gov_vet_center\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides base functionality for the Subscription Builder Queue Workers.
 */
abstract class RequiredServiceBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The term storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityStorageInterface $node_storage,
    EntityStorageInterface $term_storage
  ) {
    $this->nodeStorage = $node_storage;
    $this->termStorage = $term_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('entity_type.manager')->getStorage('taxonomy_term')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Use the "cms migrator" user account (1317).
    $author_uid = 1317;

    // Create the new node.
    $vet_center_facility_health_service_node = Node::create([
      'type' => 'vet_center_facility_health_servi',
      'status' => $data->published,
      'moderation_state' => $data->moderation_state,
      'revision_log' => $data->log_message,
      'uid' => $author_uid,
      'field_administration' => [
        ['target_id' => $data->section_id],
      ],
      'field_office' => [
        ['target_id' => $data->facility_id],
      ],
      'field_service_name_and_descripti' => [
        ['target_id' => $data->term_id],
      ],
    ]);

    $vet_center_facility_health_service_node->save();

  }

}
