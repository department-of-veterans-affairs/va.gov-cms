<?php

namespace Drupal\content_push_api\Plugin\QueueWorker;

/**
 * A Node Publisher that publishes nodes on CRON run.
 *
 * @QueueWorker(
 *   id = "content_push_queue",
 *   title = @Translation("Content Push Queue"),
 *   cron = {"time" = 5}
 * )
 */
class ContentPushQueueWorker extends ContentPushQueueBase {}
