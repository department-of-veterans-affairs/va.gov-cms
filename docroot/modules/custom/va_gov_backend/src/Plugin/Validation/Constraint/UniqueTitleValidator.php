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
    $original_comparator = $this->stripPunctuation($item->value);
    // Grab last word to use in search query.
    $last_word_comparator_raw = explode(' ', $original_comparator);
    $last_word_comparator = array_pop($last_word_comparator_raw);

    // Per internal dev meeting, dependency injection doesn't
    // work well with constraints.
    // Drupal contrib constraint modules call services directly.
    // Okay to follow this pattern.
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage->getQuery();
    $query->condition('type', 'q_a');
    $query->condition('title', '%' . $last_word_comparator . '%', 'LIKE');
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
        if ($this->isSame($original_comparator, $node->get('title')->value)) {
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
  public function stripPunctuation($string) {
    // Strip targeted punctuation items.
    // We want to keep quotes and some other things,
    // So limit what we strip.
    $remove_no_space = ['?', '!', '.', '-'];

    // Some targeted replacements.
    // Exclamation points, etc. are replaced with no space.
    // Double spaces are replaced with single space.
    $string_without_symbols = str_replace($remove_no_space, '', $string);
    $strip_final = trim(str_replace('  ', ' ', $string_without_symbols));
    return $strip_final;
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
  public function isSame($a, $b) {
    $b_formatted = $this->stripPunctuation($b);
    // Loose comparison just to be safe.
    if (strcasecmp($a, $b_formatted) == 0) {
      return TRUE;
    }

    return FALSE;
  }

}
