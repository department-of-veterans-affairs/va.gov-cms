<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for only allowing COVID-19 health services as archived.
 *
 * @Constraint(
 *   id = "ArchiveCovid19",
 *   label = @Translation("Archive COVID-19 Health Services", context = "Validation"),
 *   type = "string"
 * )
 */
class ArchiveCovid19 extends Constraint {

  /**
   * Shown if a COVID-19 health service is not be saved as archived.
   *
   * @var string
   * @see \Drupal\va_gov_vamc\Plugin\Validation\Constraint\ArchiveCovid19Validator
   */
  public $covid19Archived = 'COVID-19 vaccines health services must be archived. Please set the moderation status to \'Archived\'.';

}
