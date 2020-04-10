<?php

namespace Drupal\content_push_api\Service;

use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for handling adding a content entity to the queue.
 */
class AddToQueue {

  /**
   * AddToQueue constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue factory.
   */
  public function __construct(QueueFactory $queue) {
    $this->queue = $queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('queue')
    );
  }

  /**
   * Returns queue object.
   */
  private function getQueue() {
    return $this->queue->get('content_push_queue');
  }

  /**
   * Adds the entity the to the Drupal Queue.
   */
  public function addToQueue($data) {
    if (!empty($data)) {
      // Add item to queue.
      $queue = $this->getQueue();
      $queue->createItem($data);
    }
  }

}
