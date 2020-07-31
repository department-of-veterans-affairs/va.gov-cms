<?php

namespace Drupal\va_gov_content_export\ExportCommand;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\tome_sync\ExporterInterface;

/**
 * Command class to run export.
 *
 * @see \Drupal\tome_sync\Commands\ExportContentCommand
 */
class ExportContentCommand {
  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Tome Exporter.
   *
   * @var \Drupal\tome_sync\ExporterInterface
   */
  protected $exporter;

  /**
   * ExportContentCommand constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manger.
   * @param \Drupal\tome_sync\ExporterInterface $exporter
   *   The exporter.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ExporterInterface $exporter) {
    $this->entityTypeManager = $entityTypeManager;
    $this->exporter = $exporter;
  }

  /**
   * Export an array of id_pairs using the CMS export.
   *
   * @param array $id_pairs
   *   An array if id pairs as "entity_type:entity_id".
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\va_gov_content_export\ExportCommand\ExportCommandException
   */
  public function exportPairs(array $id_pairs) : void {
    $storages = [];
    foreach ($id_pairs as $id_pair) {
      [$entity_type_id, $id] = explode(':', $id_pair);
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if (!$entity_type) {
        throw new ExportCommandException("The entity type $entity_type_id does not exist.");
      }

      if (!isset($storages[$entity_type_id])) {
        $storages[$entity_type_id] = $this->entityTypeManager->getStorage($entity_type_id);
      }

      $entity = $storages[$entity_type_id]->load($id);
      if (!$entity) {
        throw new ExportCommandException("No entity found for $id_pair.");
      }

      if (!($entity instanceof ContentEntityInterface)) {
        throw new ExportCommandException("$id_pair is not a content entity.");
      }

      foreach ($entity->getTranslationLanguages() as $language) {
        $this->exporter->exportContent($entity->getTranslation($language->getId()));
      }
    }
  }

}
