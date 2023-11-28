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
   * Determines whether a value is valid.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node to test.
   * @param \Drupal\va_gov_vba_facility\Plugin\Validation\Constraint\VbaFacilityRequiredFieldsConstraint $constraint
   *   The constraint with which to test.
   */
  public function validate($entity, Constraint $constraint) {
    if ($entity instanceof NodeInterface && $entity->bundle() === 'vba_facility') {
      if ($entity->get('field_show_banner')->value == TRUE) {
        // To have VBA Facility banner created,
        // it must have a title and content.
        $empty_fields = [];
        $field_error_path = [];
        // Check Banner type.
        if ($entity->get('field_alert_type') && !isset($entity->get('field_alert_type')->value)) {
          // HTML5 is preventing this.
          $empty_fields[] = $entity->get('field_alert_type')->getFieldDefinition()->getLabel();
          $field_error_path[] = 'field_alert_type';
        }
        // Check Dismissible by user.
        if ($entity->get('field_dismissible_option') && !isset($entity->get('field_dismissible_option')->value)) {
          // HTML5 is preventing this.
          $empty_fields[] = $entity->get('field_dismissible_option')->getFieldDefinition()->getLabel();
          $field_error_path[] = 'field_dismissible_option';
        }
        if (empty(trim($entity->get('field_banner_title')->value))) {
          // HTML5 is preventing this.
          $empty_fields[] = $entity->get('field_banner_title')->getFieldDefinition()->getLabel();
          $field_error_path[] = 'field_banner_title';
        }
        if (empty(trim($entity->get('field_banner_content')->value))) {
          // HTML5 is not preventing this.
          $empty_fields[] = $entity->get('field_banner_content')->getFieldDefinition()->getLabel();
          // This anchor link created for the path does not work as it points to
          // a field id the ckeditor replaced with something else.
          // @see https://www.drupal.org/project/drupal/issues/3229493
          $field_error_path[] = 'field_banner_content';
        }

        if (!empty($empty_fields)) {
          if (count($field_error_path) > 1) {
            // Multiple errors should go on the checkbox.
            $field_error_path = 'field_show_banner';
          }
          else {
            // An individual error should go on the field.
            $field_error_path = $field_error_path[0];
          }
          $vars['@empty_fields'] = implode(', ', $empty_fields);
          $vars['@checkbox'] = $entity->get('field_show_banner')->getFieldDefinition()->getLabel();
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
