<?php

namespace Drupal\va_gov_batch\cbo_scripts;

require_once __DIR__ . '/../../../../../../scripts/content/script-library.php';

/**
 * Migrate Staff profile phone field to phone paragraph.
 */
class MigrateVamcFacilityMentalHealthPhoneFieldToParagraph extends MigratePhoneFieldToParagraph {

  /**
   * The source field name.
   *
   * @var string
   */
  protected string $sourceFieldName = 'field_mental_health_phone';

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-17862: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/17862.
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function preRun(&$sandbox): string {
    script_library_skip_post_api_data_check(TRUE);
    return "Data comparison during post_api_queue is now bypassed while the script runs.";
  }

  /**
   * {@inheritdoc}
   */
  public function postRun(&$sandbox): string {
    script_library_skip_post_api_data_check(FALSE);
    return "Data comparison is no longer bypassed now that the script has finished.";
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return \Drupal::entityQuery('node')
      ->condition('type', 'health_care_local_facility')
      ->accessCheck(FALSE)
      ->condition($this->sourceFieldName, operator: 'IS NOT NULL')
      ->execute();
  }

}
