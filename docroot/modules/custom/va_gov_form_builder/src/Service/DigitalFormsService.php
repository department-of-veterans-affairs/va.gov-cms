<?php

namespace Drupal\va_gov_form_builder\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service for handling Digital Form nodes.
 */
class DigitalFormsService {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a DigitalFormsService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Retrieves all Digital Form nodes.
   *
   * @param bool $publishedOnly
   *   Whether to retrieve only published nodes.
   *
   * @return \Drupal\node\NodeInterface[]
   *   An array of node objects of type 'digital_form'.
   */
  public function getDigitalForms($publishedOnly = TRUE) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'digital_form');

    if ($publishedOnly) {
      $query->condition('status', 1);
    }

    $nids = $query->execute();

    if (!empty($nids)) {
      return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    }
    return [];
  }

}
