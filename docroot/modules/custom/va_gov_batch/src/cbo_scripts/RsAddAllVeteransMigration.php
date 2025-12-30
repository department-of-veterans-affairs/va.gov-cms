<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\TermInterface;
use Drupal\va_gov_resources_and_support\Service\RsTagMigrationService;

/**
 * Migration: Add "All Veterans" when specific Veteran subtype exists.
 *
 * For R&S content types with Audience & Topics paragraphs, if a specific
 * Veteran subtype is tagged in Audience - Beneficiaries, also add
 * "All Veterans".
 *
 * To run: drush codit-batch-operations:run RsAddAllVeteransMigration
 */
class RsAddAllVeteransMigration extends BaseRsTagMigration {

  /**
   * The "All Veterans" term name.
   */
  const ALL_VETERANS_TERM = 'All Veterans';

  /**
   * Audience & Topics paragraph field for audience.
   */
  const AUDIENCE_FIELD = 'field_audience_beneficiares';

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
    return 'R&S Content: Add "All Veterans" when specific Veteran subtype exists';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return 'For R&S content types, if tagged with a specific Veteran subtype in Audience - Beneficiaries, also add "All Veterans".';
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
      self::AUDIENCE_TOPICS_FIELD,
      'R&S nodes'
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function processNode(
    Node $node,
    array $node_info,
    RsTagMigrationService $migration_service,
    array &$sandbox,
  ): string {
    if (!$node->hasField(self::AUDIENCE_TOPICS_FIELD)) {
      return "Node {$node_info['nid']} ({$node_info['title']}): No Audience & Topics field.";
    }

    $paragraphs = $this->getParagraphs($node, self::AUDIENCE_TOPICS_FIELD);
    $updated = FALSE;
    $terms_added = 0;

    foreach ($paragraphs as $paragraph) {
      if (!$paragraph->hasField(self::AUDIENCE_FIELD)) {
        continue;
      }

      // Get current audience terms filtered by vocabulary.
      $audience_terms = $this->getTermsByVocabulary(
        $paragraph->get(self::AUDIENCE_FIELD)->referencedEntities(),
        self::AUDIENCE_VOCABULARY
      );

      if (empty($audience_terms)) {
        continue;
      }

      // Check if "All Veterans" already exists.
      $has_all_veterans = FALSE;
      $has_veteran_subtype = FALSE;
      foreach ($audience_terms as $term) {
        if ($term->getName() === self::ALL_VETERANS_TERM) {
          $has_all_veterans = TRUE;
        }
        elseif ($this->isVeteranSubtype($term)) {
          $has_veteran_subtype = TRUE;
        }
      }

      // If not present and we have Veteran subtypes, add "All Veterans".
      if (!$has_all_veterans && $has_veteran_subtype) {
        $all_veterans_term = $migration_service->findTermByName(
          self::AUDIENCE_VOCABULARY,
          self::ALL_VETERANS_TERM
        );

        if ($all_veterans_term) {
          if ($this->addTermToParagraphField($paragraph, self::AUDIENCE_FIELD, $all_veterans_term)) {
            $updated = TRUE;
            $terms_added++;
          }
        }
        else {
          $migration_service->logWarning('Term "@term" not found in vocabulary @vocab', [
            '@term' => self::ALL_VETERANS_TERM,
            '@vocab' => self::AUDIENCE_VOCABULARY,
          ]);
        }
      }
    }

    if ($updated) {
      $log_message = sprintf(
        'R&S tag migration: Added "%s" to %d Audience & Topics paragraph(s)',
        self::ALL_VETERANS_TERM,
        $terms_added
      );
      $this->saveNodeRevision($node, $log_message);

      return $this->logSuccess(
        $node_info['nid'],
        $node_info['title'],
        "Successfully added \"All Veterans\" to $terms_added paragraph(s)."
      );
    }

    return "Node {$node_info['nid']} ({$node_info['title']}): No updates needed (All Veterans already present or no Veteran subtypes found).";
  }

}
