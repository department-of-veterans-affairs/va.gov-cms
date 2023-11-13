<?php

namespace Drupal\va_gov_vba_facility\Plugin\Validation\Constraint;

use Drupal\node\NodeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * That validator for the VbaFacilityRequiredFieldsConstraint.
 */
class VbaFacilityRequiredFieldsConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    /** @var \Drupal\va_gov_vba_facility\Plugin\Validation\Constraint\VbaFacilityRequiredFieldsConstraint $constraint */
    if (!isset($entity)) {
      return;
    }
    if ($entity instanceof NodeInterface && $entity->bundle() === 'vba_facility') {
      if ($entity->get('field_vba_banner_panel')->value == TRUE) {
        // To have VBA Facility banner created,
        // it must have a title and content.
        $empty_fields = [];
        $field_error_path = 'field_vba_banner_panel';
        if (empty(trim($entity->get('field_banner_title')->value))) {
          // HTML5 is preventing this.
          $empty_fields[] = $entity->get('field_banner_title')->getFieldDefinition()->getLabel();
        }
        if (empty(trim($entity->get('field_banner_content')->value))) {
          // HTML5 is not preventing this.
          $empty_fields[] = $entity->get('field_banner_content')->getFieldDefinition()->getLabel();
          // This anchor link created for the path does not work as it points to
          // a field id the ckeditor replaced with something else.
          // @see https://www.drupal.org/project/drupal/issues/3229493
          $field_error_path = 'field_banner_content';
        }

        if (!empty($empty_fields)) {
          $vars['@empty_fields'] = implode(', ', $empty_fields);
          $vars['@checkbox'] = $entity->get('field_vba_banner_panel')->getFieldDefinition()->getLabel();
          $this->context->buildViolation($constraint->message, $vars)
          // This anchor link created for the path does not work as it points to
          // #edit-field-complete-biography-create which does not exist in DOM.
          // It should point to #edit-field-complete-biography-create-value.
            ->atPath($field_error_path)
            ->addViolation();
        }
      }
    }
  }

}
