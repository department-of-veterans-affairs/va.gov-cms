<?php

namespace Drupal\va_gov_content_release\Request;

use Drupal\advancedqueue\Job;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
  public function submitRequest(string $reason, array $options) : void {
    $frontend = $options['frontend'] ?? 'content_build';
    $job_type = $frontend === 'next_build'
      ? 'va_gov_content_release_next_request'
      : 'va_gov_content_release_request';

    try {
      $job = Job::create($job_type, [
        'reason' => $reason,
        'frontend' => $frontend,
      ]);
      $this->queue->enqueueJob($job);
    }
    catch (\Throwable $exception) {
      throw new RequestException($exception->getMessage(), $exception->getCode(), $exception);
    }
  }

}
