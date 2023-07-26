<?php

namespace Drupal\va_gov_content_release\Request;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\advancedqueue\Job;
use Drupal\va_gov_content_release\Exception\RequestException;

/**
 * The Content Release Request service.
 *
 * This service actually enqueues the content release job.
 */
class Request implements RequestInterface {

  /**
   * The content release queue.
   *
   * @var \Drupal\advancedqueue\Entity\QueueInterface
   */
  protected $queue;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->queue = $entityTypeManager
      ->getStorage('advancedqueue_queue')
      ->load(static::QUEUE_NAME);
  }

  /**
   * {@inheritDoc}
   */
  public function submitRequest(string $reason) : void {
    try {
      $job = Job::create(static::JOB_TYPE, [
        'reason' => $reason,
      ]);
      $this->queue->enqueueJob($job);
    }
    catch (\Throwable $exception) {
      throw new RequestException($exception->getMessage(), $exception->getCode(), $exception);
    }
  }

}
