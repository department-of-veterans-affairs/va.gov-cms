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

    // We will use this for comparison against results.
    $original_comparator = $this->format($item->value);
    // Grab first word to use in search query.
    $first_word_comparator = strtok($original_comparator, ' ');

    // Per internal dev meeting, dependency injection doesn't
    // work well with constraints.
    // Drupal contrib constraint modules call services directly.
    // Okay to follow this pattern.
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage->getQuery();
    $query->condition('type', 'q_a');
    $query->condition('title', $first_word_comparator . '%', 'LIKE');
    // Exclude the current entity.
    if (!empty($id = $this->context->getRoot()->getEntity()->id())) {
      $query->condition('nid', $id, '!=');
    }
    $nids = $query->execute();
    $nodes = $node_storage->loadMultiple($nids);

    if (!empty($nodes)) {
      // See if we have any that match our field value.
      foreach ($nodes as $node) {
        // Compare our input title against db first word match.
        if ($this->compare($original_comparator, $node->get('title')->value)) {
          // Identical, so throw violation.
          $this->context->addViolation($constraint->notUniqueTitle, ['%title' => $node->get('title')->value, ':nid' => $node->id()]);
        }
      }
    }
  }

  /**
   * Returns formatted string for comparison.
   *
   * @return string
   *   Returns a formatted string.
   */
  public function format($string) {
    // Strip punctuation.
    $strip_punctuation = trim(preg_replace('/[^a-z0-9]+/i', ' ', $string));
    return strtolower($strip_punctuation);
  }

  /**
   * Compare original with db result.
   *
   * @param string $a
   *   The input.
   * @param string $b
   *   The db result.
   *
   * @return bool
   *   Returns TRUE is strings identical.
   */
  public function compare($a, $b) {
    $b_formatted = $this->format($b);
    // Loose comparison just to be safe.
    if ($a == $b_formatted) {
      return TRUE;
    }

    return FALSE;
  }

}
