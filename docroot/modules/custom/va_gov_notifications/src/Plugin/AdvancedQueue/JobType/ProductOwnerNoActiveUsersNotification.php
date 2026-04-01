<?php

namespace Drupal\va_gov_notifications\Plugin\AdvancedQueue\JobType;

use Drupal\va_gov_notifications\JobTypeMessageNotifyBase;

/**
 * AdvancedQueue queue processor plugin for no-active-editor notifications.
 *
 * @AdvancedQueueJobType(
 *  id = "va_gov_product_owner_no_active_users_notification",
 *  label = @Translation("Sends product owner notifications for sections without active editors."),
 *  max_retries = 3,
 *  retry_delay = 10
 * )
 */
class ProductOwnerNoActiveUsersNotification extends JobTypeMessageNotifyBase {}
