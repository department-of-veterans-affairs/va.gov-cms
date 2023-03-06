<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Validates the SectionMatcher constraint.
 */
class SectionMatcherValidator extends ConstraintValidator {

  protected function getSection(string $nid) {
    // $entity = load node from $nid
    // check for field_administration
    $node_type = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_type->load($nid);
    $section = $node->field_administration->target_id;
    return $section;
    // if field_administration, return $entity->field_administration->target_id
    // $value->entityTypeManager->getStorage('node')->load($entity->field_administration->target_id)
  }
  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (!$value->hasField('field_administration')) {
      return;
    }
    $section = \Drupal::entityTypeManager()->getStorage('node')->load($value->nid->value)->field_administration->target_id;
    $facility_section = \Drupal::entityTypeManager()->getStorage('node')->load($value->field_facility_location->target_id)->field_administration->target_id;
    $service_section = \Drupal::entityTypeManager()->getStorage('node')->load($value->field_regional_health_service->target_id)->field_administration->target_id;
    if ($facility_section !== $section && $service_section !== $section) {
      $this->context->addViolation($constraint->notSectionMatchEither);
    }
    if ($facility_section !== $section) {
      $this->context->addViolation($constraint->notSectionMatchFacility);
    }
    if ($service_section !== $section) {
      $this->context->addViolation($constraint->notSectionMatchService);
    }
    //     $this->context->addViolation($constraint->notSectionMatchFacility);



    // If $facility name does not contain section name
    // print: The Facility does not match the Section. Please select a matching option.

    // If VAMC system health service does not contain section name
    // print: The VAMC system health service does not match the Section. Please select a matching option.

    // If neither not contain section name
    // print: The Facility and VAMC system health service do not match the section. Please select matching options.

    // foreach ($items as $item) {
    //   /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\ValidPhoneNumber $constraint */
    //   // Greater than 9 numbers, so we do a phone number check.
    //   if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $item->value) && strlen($item->value) > 9) {
    //     $this->context->addViolation($constraint->notValidTel, ['%value' => $item->value]);
    //   }
    //   // Length is 5 or 6, so do a shortcode check.
    //   elseif ((!preg_match("/^\d{5}$/", $item->value) && strlen($item->value) === 5) || (!preg_match("/^\d{6}$/", $item->value)&&strlen($item->value) === 6)) {
    //     $this->context->addViolation($constraint->notValidSms, ['%value' => $item->value]);
    //   }
    //   // Length is 3, so do a TTY check.
    //   elseif ((!preg_match("/^\d{3}$/", $item->value)) && (strlen($item->value) === 3)) {
    //     $this->context->addViolation($constraint->notValidTty, ['%value' => $item->value]);
    //   }
    // }
  }

}
