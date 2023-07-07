<?php

namespace Drupal\va_gov_profile\Plugin\Validation\Constraint;

use Drupal\node\NodeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * That validator for the PersonPageRequiredFieldsConstraint.
 */
class PersonPageRequiredFieldsConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }
    if ($entity instanceof NodeInterface && $entity->bundle() === 'person_profile') {
      if ($entity->get('field_complete_biography_create')->value == TRUE) {
        // To have bio page created, it must have both fields populated.
        $empty_fields = [];
        $field_error_path = 'field_complete_biography_create';
        if (empty(trim($entity->get('field_intro_text')->value))) {
          // HTML5 is preventing this.
          $empty_fields[] = $entity->get('field_intro_text')->getFieldDefinition()->getLabel();
        }
        if (empty(trim($entity->get('field_body')->value))) {
          // HTML5 is not preventing this.
          $empty_fields[] = $entity->get('field_body')->getFieldDefinition()->getLabel();
          // This anchor link created for the path does not work as it points to
          // a field id the ckeditor replaced with something else.
          // @see https://www.drupal.org/project/drupal/issues/3229493
          $field_error_path = 'field_body';
        }

        if (!empty($empty_fields)) {
          $vars['@empty_fields'] = implode(', ', $empty_fields);
          $vars['@checkbox'] = $entity->get('field_complete_biography_create')->getFieldDefinition()->getLabel();
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
