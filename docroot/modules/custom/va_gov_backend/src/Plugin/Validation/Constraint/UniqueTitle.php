<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a unique title.
 *
 * @Constraint(
 *   id = "UniqueTitle",
 *   label = @Translation("Unique Title", context = "Validation"),
 *   type = "string"
 * )
 */
class UniqueTitle extends Constraint {

  /**
   * The message that will be shown if the value is not a unique title.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $notUniqueTitle = 'Q&amp;A with title "%title" already exists. <a target="_blank" href="/node/:nid">Check the existing "%title" before creating a new one.</a>';

}
