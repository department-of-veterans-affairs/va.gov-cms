<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\TermInterface;

require_once __DIR__ . '/../../../../../../scripts/content/script-library.php';

/**
 * Migration: VA Benefits Taxonomy - Add "All Veterans".
 *
 * For VA Benefits taxonomy terms, if tagged with a specific Veteran subtype
 * in the Beneficiaries field (field_va_benefit_beneficiaries) referencing
 * the Audience - Beneficiaries Taxonomy, also add "All Veterans".
 *
 * To run: drush codit-batch-operations:run VaBenefitsTaxonomyMigration
 */
class VaBenefitsTaxonomyMigration extends BaseRsTagMigration {

  /**
   * The "All Veterans" term name.
   */
  const ALL_VETERANS_TERM = 'All Veterans';

  /**
   * VA Benefits Beneficiaries field name.
   */
  const BENEFICIARIES_FIELD = 'field_va_benefit_beneficiaries';

  /**
   * VA Benefits taxonomy vocabulary ID.
   */
  const VA_BENEFITS_VOCABULARY = 'va_benefits_taxonomy';

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
   * Get all taxonomy terms from a taxonomy term field.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The taxonomy term.
   * @param string $field_name
   *   The field name.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   *   Array of taxonomy term entities.
   */
  protected function getTermFieldTerms(TermInterface $term, string $field_name): array {
    $terms = [];
    if ($term->hasField($field_name)) {
      foreach ($term->get($field_name)->referencedEntities() as $referenced_term) {
        if ($referenced_term instanceof TermInterface) {
          $terms[] = $referenced_term;
        }
      }
    }
    return $terms;
  }

  /**
   * Add terms to a taxonomy term field (additive, not overwriting).
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The taxonomy term.
   * @param string $field_name
   *   The field name.
   * @param \Drupal\taxonomy\TermInterface[] $terms_to_add
   *   Terms to add.
   *
   * @return bool
   *   TRUE if terms were added, FALSE otherwise.
   */
  protected function addTermsToTermField(TermInterface $term, string $field_name, array $terms_to_add): bool {
    if (!$term->hasField($field_name)) {
      $this->batchOpLog->appendError(sprintf('Field %s does not exist on term %d', $field_name, $term->id()));
      return FALSE;
    }

    $existing_terms = $this->getTermFieldTerms($term, $field_name);
    $existing_tids = array_map(function (TermInterface $existing_term) {
      return $existing_term->id();
    }, $existing_terms);

    $new_terms = [];
    foreach ($terms_to_add as $term_to_add) {
      if (!in_array($term_to_add->id(), $existing_tids)) {
        $new_terms[] = $term_to_add;
      }
    }

    if (empty($new_terms)) {
      return FALSE;
    }

    // Get current field values and add the new terms.
    $field_values = $term->get($field_name)->getValue();
    foreach ($new_terms as $new_term) {
      $field_values[] = ['target_id' => $new_term->id()];
    }

    $term->set($field_name, $field_values);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'VA Benefits Taxonomy: Add "All Veterans" when specific Veteran subtype exists';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return 'For VA Benefits taxonomy terms, if tagged with a specific Veteran subtype in Beneficiaries field (Audience - Beneficiaries Taxonomy), also add "All Veterans".';
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@completed out of @total VA Benefits taxonomy terms processed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'VA Benefits taxonomy term';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    try {
      $query = $this->entityTypeManager->getStorage('taxonomy_term')
        ->getQuery()
        ->condition('vid', self::VA_BENEFITS_VOCABULARY)
        ->condition(self::BENEFICIARIES_FIELD, NULL, 'IS NOT NULL')
        ->accessCheck(FALSE);

      return $query->execute();
    }
    catch (\Exception $e) {
      $message = sprintf('Error gathering VA Benefits taxonomy terms: %s', $e->getMessage());
      $this->batchOpLog->appendError($message);
      return [];
    }
  }

  /**
   * {@inheritdoc}
   *
   * This migration processes taxonomy terms, not nodes, so this method
   * should never be called. It's required because the base class defines
   * processNode() as abstract.
   *
   * @throws \BadMethodCallException
   *   Always throws, as this migration doesn't process nodes.
   */
  protected function processNode(Node $node, array &$sandbox): string {
    throw new \BadMethodCallException('VaBenefitsTaxonomyMigration processes taxonomy terms, not nodes. Use processOne() instead.');
  }

  /**
   * {@inheritdoc}
   *
   * Override to handle taxonomy terms instead of nodes.
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    try {
      /** @var \Drupal\taxonomy\TermInterface|null $term */
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($item);

      if (!$term) {
        return sprintf('Term %s not found, skipped.', $item);
      }

      if (!$term->hasField(self::BENEFICIARIES_FIELD)) {
        return sprintf('Term %d (%s): No Beneficiaries field.', $term->id(), $term->getName());
      }

      // Get current beneficiary terms from the field.
      $all_terms = $this->getTermFieldTerms($term, self::BENEFICIARIES_FIELD);

      // Filter to only Beneficiaries taxonomy terms.
      $beneficiary_terms = $this->getTermsByVocabulary($all_terms, self::AUDIENCE_VOCABULARY);

      if (empty($beneficiary_terms)) {
        return sprintf('Term %d (%s): No Beneficiaries taxonomy terms found.', $term->id(), $term->getName());
      }

      // Check if "All Veterans" already exists and if we have Veteran subtypes.
      $has_all_veterans = FALSE;
      $has_veteran_subtype = FALSE;

      foreach ($beneficiary_terms as $beneficiary_term) {
        if ($beneficiary_term->getName() === self::ALL_VETERANS_TERM) {
          $has_all_veterans = TRUE;
        }
        elseif ($this->isVeteranSubtype($beneficiary_term)) {
          $has_veteran_subtype = TRUE;
        }
      }

      // If "All Veterans" is not present and we have Veteran subtypes, add it.
      if (!$has_all_veterans && $has_veteran_subtype) {
        $all_veterans_term = find_term_by_name(self::AUDIENCE_VOCABULARY, self::ALL_VETERANS_TERM);

        if ($all_veterans_term) {
          $added = $this->addTermsToTermField($term, self::BENEFICIARIES_FIELD, [$all_veterans_term]);

          if ($added) {
            // Save the term with revision tracking.
            $term->setNewRevision(TRUE);
            $term->setRevisionUserId(CMS_MIGRATOR_ID);
            $term->setRevisionCreationTime(time());
            $term->setChangedTime(time());
            $term->setRevisionLogMessage(sprintf(
              'VA Benefits tag migration: Added "%s" to Beneficiaries field',
              self::ALL_VETERANS_TERM
            ));
            $term->save();

            return $this->logSuccess(
              $term->id(),
              $term->getName(),
              sprintf('Successfully added "%s" to Beneficiaries field.', self::ALL_VETERANS_TERM)
            );
          }
          else {
            return sprintf('Term %d (%s): Failed to add "All Veterans" (may already exist).', $term->id(), $term->getName());
          }
        }
        else {
          $this->batchOpLog->appendLog(sprintf('Term "%s" not found in vocabulary %s for term %d', self::ALL_VETERANS_TERM, self::AUDIENCE_VOCABULARY, $term->id()));
          return sprintf('Term %d (%s): "All Veterans" term not found in vocabulary.', $term->id(), $term->getName());
        }
      }

      return sprintf('Term %d (%s): No updates needed (All Veterans already present or no Veteran subtypes found).', $term->id(), $term->getName());
    }
    catch (\Exception $e) {
      $message = sprintf('Error processing term ID %s: %s', $item, $e->getMessage());
      $this->batchOpLog->appendError($message);
      return sprintf('Error processing term %s.', $item);
    }
  }

  /**
   * Log success message for a processed term.
   *
   * @param int $tid
   *   The term ID.
   * @param string $term_name
   *   The term name.
   * @param string $success_message
   *   The success message.
   *
   * @return string
   *   Formatted success message.
   */
  protected function logSuccess(int $tid, string $term_name, string $success_message): string {
    $message = sprintf('Term %d (%s): %s', $tid, $term_name, $success_message);
    $this->batchOpLog->appendLog($message);
    return sprintf('Term %d processed successfully.', $tid);
  }

}
