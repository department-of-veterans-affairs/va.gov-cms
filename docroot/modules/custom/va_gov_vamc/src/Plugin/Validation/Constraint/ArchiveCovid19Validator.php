<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ArchiveCovid19 constraint.
 */
class ArchiveCovid19Validator extends ConstraintValidator {

  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    $entity = $value->getEntity();
    $bundle = $entity->bundle();
    $moderationState = $entity->moderation_state->value;

    // If a COVID-19 health service is not archived, add a violation.
    if ($moderationState !== 'archived') {
      if ($bundle === 'regional_health_care_service_des') {
        $tid = $entity->field_service_name_and_descripti->target_id;
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
        if ($term && str_contains($term->label(), 'COVID-19 vaccines') !== FALSE) {
          /** @var \Drupal\va_gov_vamc\Plugin\Validation\Constraint\ArchiveCovid19 $constraint */
          $this->getContext()
            ->buildViolation($constraint->covid19Archived, [])
            ->addViolation();
        }
      }
      elseif ($bundle === 'health_care_local_health_service') {
        $nid = $entity->field_regional_health_service->target_id;
        $referencedNode = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
        if ($referencedNode && str_contains($referencedNode->label(), 'COVID-19 vaccines') !== FALSE) {
          /** @var \Drupal\va_gov_vamc\Plugin\Validation\Constraint\ArchiveCovid19 $constraint */
          $this->getContext()
            ->buildViolation($constraint->covid19Archived, [])
            ->addViolation();
        }
      }
    }
  }

}
