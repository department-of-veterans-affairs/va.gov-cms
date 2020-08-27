<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueTitle constraint.
 */
class UniqueTitleValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {

    // Grab first word to use in search query.
    $lower_case_value = strtolower($item->value);
    $first_word = strtok($lower_case_value, ' ');

    // Per internal dev meeting, dependency injection doesn't
    // work well with constraints.
    // Drupal contrib constraint modules call services directly.
    // Okay to follow this pattern.
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage->getQuery();
    $query->condition('type', 'q_a');
    $query->condition('title', $first_word . '%', 'LIKE');
    // Exclude the current entity.
    if (!empty($id = $this->context->getRoot()->getEntity()->id())) {
      $query->condition('nid', $id, '!=');
    }
    $nids = $query->execute();
    $nodes = $node_storage->loadMultiple($nids);

    if (!empty($nodes)) {
      // See if we have any that match our field value.
      foreach ($nodes as $node) {
        // Loose comparison just to be safe.
        if ($lower_case_value == strtolower($node->get('title')->value)) {
          $this->context->addViolation($constraint->notUniqueTitle, ['%title' => $item->value, ':nid' => $node->id()]);
        }
      }
    }

  }

}
