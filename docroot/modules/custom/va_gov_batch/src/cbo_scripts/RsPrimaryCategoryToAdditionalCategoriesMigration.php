<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\TermInterface;
use Drupal\va_gov_batch\BaseRsTagMigration;

/**
 * Copy R&S Primary category into Additional categories (same vocabulary).
 *
 * For all seven R&S content types, the single lc_categories term on
 * field_primary_category is appended to field_other_categories if not already
 * present. field_primary_category is left unchanged (removed in a follow-up).
 *
 * field_other_categories allows up to six terms; nodes already at the limit
 * without the primary term receive an error log and no change.
 *
 * @code
 * drush codit-batch-operations:run
 * RsPrimaryCategoryToAdditionalCategoriesMigration
 * @endcode
 */
class RsPrimaryCategoryToAdditionalCategoriesMigration extends BaseRsTagMigration {

  /**
   * Primary category field name.
   */
  const PRIMARY_CATEGORY_FIELD = 'field_primary_category';

  /**
   * Additional categories field name.
   */
  const ADDITIONAL_CATEGORY_FIELD = 'field_other_categories';

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'R&S Content: Copy Primary category to Additional categories';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return 'For all R&S content types, copies the Benefit/R&S category from Primary category into Additional categories when missing, without removing Primary.';
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@completed out of @total R&S nodes processed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'R&S content node';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return $this->gatherNodesByType(
      self::RS_CONTENT_TYPES,
      self::PRIMARY_CATEGORY_FIELD,
      'R&S nodes with primary category'
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function processNode(Node $node, array &$sandbox): string {
    $primary_terms = get_node_field_terms($node, self::PRIMARY_CATEGORY_FIELD);
    $primary_terms = $this->getTermsByVocabulary($primary_terms, self::RS_CATEGORIES_VOCABULARY);

    if (empty($primary_terms)) {
      return sprintf(
        'Node %d (%s): No %s term on primary category; skipped.',
        $node->id(),
        $node->getTitle(),
        self::RS_CATEGORIES_VOCABULARY
      );
    }

    /** @var \Drupal\taxonomy\TermInterface $primary_term */
    $primary_term = reset($primary_terms);
    $primary_tid = (int) $primary_term->id();

    $additional_terms = $node->hasField(self::ADDITIONAL_CATEGORY_FIELD)
      ? get_node_field_terms($node, self::ADDITIONAL_CATEGORY_FIELD)
      : [];
    $additional_tids = array_map(static function (TermInterface $t) {
      return (int) $t->id();
    }, $additional_terms);

    if (in_array($primary_tid, $additional_tids, TRUE)) {
      return sprintf(
        'Node %d (%s): Primary category "%s" already in additional categories; no change.',
        $node->id(),
        $node->getTitle(),
        $primary_term->getName()
      );
    }

    $cardinality = $this->getFieldCardinality('node', $node->bundle(), self::ADDITIONAL_CATEGORY_FIELD);
    if ($cardinality > 0 && count($additional_terms) >= $cardinality) {
      $msg = sprintf(
        'Node %d (%s): Additional categories at cardinality limit (%d); cannot add primary "%s" (tid %d).',
        $node->id(),
        $node->getTitle(),
        $cardinality,
        $primary_term->getName(),
        $primary_tid
      );
      $this->batchOpLog->appendError($msg);
      return $msg;
    }

    $added = $this->addTermsToNodeField($node, self::ADDITIONAL_CATEGORY_FIELD, [$primary_term]);
    if (!$added) {
      $msg = sprintf(
        'Node %d (%s): Failed to add primary "%s" to additional categories (see batch log).',
        $node->id(),
        $node->getTitle(),
        $primary_term->getName()
      );
      $this->batchOpLog->appendError($msg);
      return $msg;
    }

    $log_message = sprintf(
      'R&S primary-to-additional migration: Copied term "%s" (tid %d) from %s to %s',
      $primary_term->getName(),
      $primary_tid,
      self::PRIMARY_CATEGORY_FIELD,
      self::ADDITIONAL_CATEGORY_FIELD
    );
    $this->saveNodeRevision($node, $log_message);

    return $this->logSuccess(
      $node->id(),
      $node->getTitle(),
      sprintf('Copied primary category "%s" into additional categories.', $primary_term->getName())
    );
  }

}
