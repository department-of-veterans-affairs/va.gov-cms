<?php


namespace Drupal\va_gov_content_export;


use Drupal\tome_sync\Exporter;

class TomeExporter extends Exporter {
  /**
   * An array of excluded entity types.
   *
   * @var string[]
   */
  protected static $excludedTypes = [
    'content_moderation_state',
    'user',
    'user_role',
    'user_history',
  ];
}
