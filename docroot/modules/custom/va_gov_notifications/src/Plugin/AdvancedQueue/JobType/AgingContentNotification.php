<?php

namespace Drupal\va_gov_notifications\Plugin\AdvancedQueue\JobType;

use Drupal\va_gov_notifications\JobTypeMessageNotifyBase;

/**
 * AdvancedQueue base queue processor plugin for VA.gov email notifications.
 *
 * @AdvancedQueueJobType(
 *  id = "va_gov_aging_content_notification",
 *  label = @Translation("Sends email notifications about aging content for VA.gov"),
 *  max_retries = 3,
 *  retry_delay = 10
 * )
 */
class AgingContentNotification extends JobTypeMessageNotifyBase {}
