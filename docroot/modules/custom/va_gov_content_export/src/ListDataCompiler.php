<?php

namespace Drupal\va_gov_content_export;

use Drupal;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ListDataCompiler.
 *
 * @package Drupal\va_gov_content_export
 */
class ListDataCompiler {

  /**
   * A Drupal file system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * A Symfony serializer object.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * An array of excluded entity types.
   *
   * @var string[]
   */
  protected static $excludedTypes = [

  ];

  /**
   * An array of field names to treat as a reference to a list.
   *
   * @var array
   */
  protected static $listingFields = [
    'field_listing',
  ];

  /**
   * ListDataCompiler constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   Drupal FileSystem.
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   The serializer.
   */
  public function __construct(FileSystemInterface $fileSystem, Serializer $serializer) {
    $this->fileSystem = $fileSystem;
    $this->serializer = $serializer;
  }



  /**
   * Generate lists export files.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity which may reference lists.
   */
  public function generateLists(ContentEntityInterface $entity) : void {
    $listNids = $this->getListNids($entity);
    if (!empty($listNids)) {
      // Process each list this entity should appear in.
      foreach ($listNids as $listNid) {
        $this->generateList($entity, $listNid);
      }
    }

  }


  /**
   * Generate a single list json with a UID matching the list nid.
   *
   * This creates the file in the pattern
   * list.UUID.json
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is in a list.
   * @param int $list_nid
   *   The node id of ths list this entity belongs in.
   */
  public function $generateList(ContentEntityInterface $entity, $listNid) {
   $listUUID = $this->lookupListUUID($listNid);
   $listItems = $this->queryListItemNodes();
   $this->writeListFile($listItems, $listUUID);
  }

  /**
   * Checks if this entity belongs in a list.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check.
   *
   * @return array
   *   An array of node ids, one for each list the entity belongs in.
   */
  protected function getListNids(ContentEntityInterface $entity) : bool {
    $listNids = [];

    foreach (static::$listingFields as $listingField) {
      if ($entity->hasField($listingField)) {
        // It has a field that is a reference to a list.
        $lists = $entity->get($listingField)->value;
        $listNids = array_merge($listNids, $lists);
      }
    }

    return $listNids;
  }



  protected function writeListFile($listItems, $listUUID) {
    $content = $this->buildContent();
    // Do stuff to put content in the file.
    $filename = "list.{$listUUID}.json";
  }

  protected function buildContent($listItems, $listUUID) {
    $url = $entity->toUrl();
  }

}
