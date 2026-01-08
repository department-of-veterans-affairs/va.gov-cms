<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\TermInterface;

/**
 * Migration: CLP - Add "All Veterans" when specific Veteran subtype exists.
 *
 * For CLP (Campaign Landing Page) content types, if tagged with a specific
 * Veteran subtype in the Select Audience field (field_clp_audience) referencing
 * the Beneficiaries Taxonomy, also add "All Veterans".
 *
 * To run: drush codit-batch-operations:run ClpVaBenefitsMigration
 */
class ClpVaBenefitsMigration extends BaseRsTagMigration {

  /**
   * The "All Veterans" term name.
   */
  const ALL_VETERANS_TERM = 'All Veterans';

  /**
   * CLP Select Audience field name.
   */
  const CLP_AUDIENCE_FIELD = 'field_clp_audience';

  /**
   * Check if a term is a Veteran subtype (not "All Veterans").
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term to check.
   *
   * @return bool
   *   TRUE if the term is a Veteran subtype, FALSE otherwise.
   */
  protected function isVeteranSubtype(TermInterface $term): bool {
    $term_name = $term->getName();
    // Exclude "All Veterans" itself.
    if ($term_name === self::ALL_VETERANS_TERM) {
      return FALSE;
    }
    // Check if the term name contains "Veteran" (case-insensitive).
    return stripos($term_name, 'Veteran') !== FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'CLP: Add "All Veterans" when specific Veteran subtype exists';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return 'For CLP content types, if tagged with a specific Veteran subtype in Select Audience (Beneficiaries Taxonomy), also add "All Veterans".';
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@completed out of @total CLP nodes processed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'campaign_landing_page node';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return $this->gatherNodesByType('campaign_landing_page', self::CLP_AUDIENCE_FIELD, 'CLP nodes');
  }

  /**
   * {@inheritdoc}
   */
  protected function processNode(Node $node, array &$sandbox): string {
    if (!$node->hasField(self::CLP_AUDIENCE_FIELD)) {
      return sprintf('Node %d (%s): No Select Audience field.', $node->id(), $node->getTitle());
    }

    // Get current audience terms from the field.
    $all_terms = get_node_field_terms($node, self::CLP_AUDIENCE_FIELD);

    // Filter to only Beneficiaries taxonomy terms.
    $beneficiary_terms = $this->getTermsByVocabulary($all_terms, self::AUDIENCE_VOCABULARY);

    if (empty($beneficiary_terms)) {
      return sprintf('Node %d (%s): No Beneficiaries taxonomy terms found.', $node->id(), $node->getTitle());
    }

    // Check if "All Veterans" already exists and if we have Veteran subtypes.
    $has_all_veterans = FALSE;
    $has_veteran_subtype = FALSE;

    foreach ($beneficiary_terms as $term) {
      if ($term->getName() === self::ALL_VETERANS_TERM) {
        $has_all_veterans = TRUE;
      }
      elseif ($this->isVeteranSubtype($term)) {
        $has_veteran_subtype = TRUE;
      }
    }

    // If "All Veterans" is not present and we have Veteran subtypes, add it.
    if (!$has_all_veterans && $has_veteran_subtype) {
      $all_veterans_term = find_term_by_name(self::AUDIENCE_VOCABULARY, self::ALL_VETERANS_TERM);

      if ($all_veterans_term) {
        $added = $this->addTermsToNodeField($node, self::CLP_AUDIENCE_FIELD, [$all_veterans_term]);

        if ($added) {
          $log_message = sprintf(
            'CLP tag migration: Added "%s" to Select Audience field',
            self::ALL_VETERANS_TERM
          );
          $this->saveNodeRevision($node, $log_message);

          return $this->logSuccess(
            $node->id(),
            $node->getTitle(),
            sprintf('Successfully added "%s" to Select Audience field.', self::ALL_VETERANS_TERM)
          );
        }
        else {
          return sprintf('Node %d (%s): Failed to add "All Veterans" (field may be at cardinality limit).', $node->id(), $node->getTitle());
        }
      }
      else {
        $this->batchOpLog->appendLog(sprintf('Term "%s" not found in vocabulary %s for node %d', self::ALL_VETERANS_TERM, self::AUDIENCE_VOCABULARY, $node->id()));
        return sprintf('Node %d (%s): "All Veterans" term not found in vocabulary.', $node->id(), $node->getTitle());
      }
    }

    return sprintf('Node %d (%s): No updates needed (All Veterans already present or no Veteran subtypes found).', $node->id(), $node->getTitle());
  }

}
