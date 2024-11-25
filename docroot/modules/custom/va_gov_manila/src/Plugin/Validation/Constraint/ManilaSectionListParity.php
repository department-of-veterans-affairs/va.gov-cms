<?php

namespace Drupal\va_gov_manila\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Validates that a Manila Section choice matches the listing page choice.
 */
#[Constraint(
  id: 'ManilaSectionListParity',
  label: new TranslatableMarkup('Manila Section List Parity', [], ['context' => 'Validation']),
  type: 'string'
)]
class ManilaSectionListParity extends SymfonyConstraint {

  /**
   * The message shown if the listing page does not match the Manila section.
   *
   * @var string
   * @see \Drupal\va_gov_Manila\Plugin\Validation\Constraint\ManilaSectionListParityValidator
   */
  public $notSectionListMatch = 'The selected option for <strong>%fieldLabel</strong> is not part of the <strong>%section</strong> section. Please choose a different option or change the section settings to match.';

}
