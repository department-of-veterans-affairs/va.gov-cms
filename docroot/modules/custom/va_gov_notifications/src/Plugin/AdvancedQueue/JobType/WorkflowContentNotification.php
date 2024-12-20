<?php

namespace Drupal\va_gov_notifications\Plugin\AdvancedQueue\JobType;

use Drupal\va_gov_notifications\JobTypeMessageNotifyBase;

/**
 * AdvancedQueue queue processor plugin for Content Workflow notifications.
 *
 * @AdvancedQueueJobType(
 *  id = "va_gov_workflow_content_notification",
 *  label = @Translation("Sends email notifications for Content Workflow changes."),
 *  max_retries = 3,
 *  retry_delay = 10
 * )
 */
class WorkflowContentNotification extends JobTypeMessageNotifyBase {}
