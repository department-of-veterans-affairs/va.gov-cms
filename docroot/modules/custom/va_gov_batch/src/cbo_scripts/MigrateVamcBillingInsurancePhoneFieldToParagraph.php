<?php

namespace Drupal\va_gov_batch\cbo_scripts;

/**
 * Migrate Staff profile phone field to phone paragraph.
 */
class MigrateVamcBillingInsurancePhoneFieldToParagraph extends MigratePhoneFieldToParagraph {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-17861: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/17861.
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return \Drupal::entityQuery('node')
      ->condition('type', 'vamc_system_billing_insurance')
      ->accessCheck(FALSE)
      ->condition($this->sourceFieldName, operator: 'IS NOT NULL')
      ->execute();
  }

}
