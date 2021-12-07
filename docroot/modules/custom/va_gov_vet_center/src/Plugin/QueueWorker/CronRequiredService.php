<?php

namespace Drupal\va_gov_vet_center\Plugin\QueueWorker;

/**
 * A queue worker that creates required services on CRON run.
 *
 * @QueueWorker(
 *   id = "cron_required_service",
 *   title = @Translation("Cron Required Service"),
 *   cron = {"time" = 180}
 * )
 */
class CronRequiredService extends RequiredServiceBase {}
