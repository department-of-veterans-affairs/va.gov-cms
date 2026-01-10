<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;

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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  protected function processNode(Node $node, array &$sandbox): string {
    if (!$node->hasField(self::AUDIENCE_TOPICS_FIELD)) {
      return sprintf('Node %d (%s): No Audience & Topics field.', $node->id(), $node->getTitle());
    }

    $field = $node->get(self::AUDIENCE_TOPICS_FIELD);
    $updated = FALSE;
    $terms_added = 0;

    // Work directly with the node's field items instead of extracting
    // paragraphs.
    foreach ($field as $delta => $field_item) {
      /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $field_item */
      $paragraph = $field_item->entity;
      if (!$paragraph instanceof ParagraphInterface || !$paragraph->hasField(self::AUDIENCE_FIELD)) {
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
        $all_veterans_term = find_term_by_name(self::AUDIENCE_VOCABULARY, self::ALL_VETERANS_TERM);

        if ($all_veterans_term) {
          if ($this->addTermToParagraphField($paragraph, self::AUDIENCE_FIELD, $all_veterans_term)) {
            // Update the node's field reference to point to the new paragraph
            // revision.
            $this->updateParagraphFieldRevision($node, self::AUDIENCE_TOPICS_FIELD, $delta, $paragraph);
            $updated = TRUE;
            $terms_added++;
          }
        }
        else {
          $this->batchOpLog->appendLog(sprintf('Term "%s" not found in vocabulary %s', self::ALL_VETERANS_TERM, self::AUDIENCE_VOCABULARY));
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
        $node->id(),
        $node->getTitle(),
        sprintf('Successfully added "All Veterans" to %d paragraph(s).', $terms_added)
      );
    }

    return sprintf('Node %d (%s): No updates needed (All Veterans already present or no Veteran subtypes found).', $node->id(), $node->getTitle());
  }

}
