<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for validating menu parent links.
 *
 * @Constraint(
 *   id = "MenuParentLink",
 *   label = @Translation("Menu Parent Link", context = "Validation"),
 *   type = "entity"
 * )
 */
class MenuParentLink extends Constraint {

  /**
   * Shown if the Parent Link is set to the root menu item.
   *
   * @var string
   * @see \Drupal\va_gov_vamc\Plugin\Validation\Constraint\MenuParentLinkValidator
   */
  public $parentLink = 'Unable to publish without a valid Parent link. Please ensure the menu settings for this facility are set up correctly.';

}
