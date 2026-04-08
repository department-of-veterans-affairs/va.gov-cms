<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\migrate\Attribute\MigrateDestination;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Row;
use Drupal\va_gov_migrate\Plugin\Derivative\MigrateEntityPreserveDraft;

/**
 * Destination plugin that preserves forward drafts during migration.
 *
 * Extends the standard entity:ENTITY_TYPE destination to detect and preserve
 * non-default draft revisions. After the normal default-revision save, any
 * forward draft is carried forward as a new non-default revision with
 * migration-owned fields copied from the freshly saved default revision.
 *
 * Uses the existing overwrite_properties configuration to determine which
 * fields are migration-owned. Revision metadata properties are automatically
 * excluded from the draft carry-forward.
 *
 * Example:
 * @code
 * destination:
 *   plugin: entity_preserve_draft:node
 *   default_bundle: va_form
 *   overwrite_properties:
 *     - title
 *     - field_va_form_number
 *     - revision_default
 *     - changed
 * @endcode
 */
#[MigrateDestination(
  id: 'entity_preserve_draft',
  deriver: MigrateEntityPreserveDraft::class
)]
class EntityPreserveDraft extends EntityContentBase {

  /**
   * Revision metadata properties excluded from draft carry-forward.
   *
   * These are set explicitly by replayDraft() or updated automatically on
   * entity save, so they should not be copied from the default revision.
   */
  private const REVISION_METADATA_PROPERTIES = [
    'changed',
    'new_revision',
    'revision_default',
    'revision_log',
    'revision_timestamp',
    'revision_uid',
    'uid',
  ];

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $forward_draft_context = $this->captureForwardDraft($old_destination_id_values, $row);

    $ids = parent::import($row, $old_destination_id_values);

    if ($ids && $forward_draft_context) {
      $this->replayDraft($ids, $forward_draft_context);
    }

    return $ids;
  }

  /**
   * Capture the forward draft revision before the default revision is saved.
   *
   * @param array $old_destination_id_values
   *   The old destination ID values.
   * @param \Drupal\migrate\Row $row
   *   The current row.
   *
   * @return array|null
   *   An array with 'revision_id' if a forward draft exists, or NULL.
   */
  protected function captureForwardDraft(array $old_destination_id_values, Row $row): ?array {
    $entity_id = reset($old_destination_id_values) ?: $this->getEntityId($row);
    if (empty($entity_id)) {
      return NULL;
    }

    $entity = $this->storage->load($entity_id);
    if (!$entity instanceof RevisionableInterface) {
      return NULL;
    }

    assert($this->storage instanceof RevisionableStorageInterface);
    $latest_revision_id = (int) $this->storage->getLatestRevisionId($entity_id);
    if (empty($latest_revision_id) || $latest_revision_id === (int) $entity->getRevisionId()) {
      return NULL;
    }

    $latest_revision = $this->storage->loadRevision($latest_revision_id);
    if (!$latest_revision instanceof RevisionableInterface || $latest_revision->isDefaultRevision()) {
      return NULL;
    }

    return ['revision_id' => $latest_revision_id];
  }

  /**
   * Replay migration-owned fields onto a new non-default draft revision.
   *
   * @param array $ids
   *   The destination IDs returned from the default revision save.
   * @param array $forward_draft_context
   *   Context from captureForwardDraft() containing 'revision_id'.
   */
  protected function replayDraft(array $ids, array $forward_draft_context): void {
    $overwrite_properties = array_diff(
      $this->configuration['overwrite_properties'] ?? [],
      self::REVISION_METADATA_PROPERTIES
    );
    if (empty($overwrite_properties)) {
      return;
    }

    $entity_id = $ids[0];
    $this->storage->resetCache([$entity_id]);
    $default_revision = $this->storage->load($entity_id);
    $draft_revision = $this->storage->loadRevision($forward_draft_context['revision_id']);

    if (!$default_revision instanceof FieldableEntityInterface || !$draft_revision instanceof FieldableEntityInterface) {
      return;
    }

    foreach ($overwrite_properties as $property) {
      if ($default_revision->hasField($property) && $draft_revision->hasField($property)) {
        $draft_revision->set($property, $default_revision->get($property)->getValue());
      }
    }

    $draft_revision->setNewRevision(TRUE);
    $draft_revision->enforceIsNew(FALSE);
    $draft_revision->setValidationRequired(FALSE);
    $draft_revision->isDefaultRevision(FALSE);
    $draft_revision->setRevisionLogMessage('Draft revision carried forward after migration.');
    $draft_revision->save();
  }

}
