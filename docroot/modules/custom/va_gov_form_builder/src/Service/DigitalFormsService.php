<?php

namespace Drupal\va_gov_form_builder\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;

/**
 * Service for fetching and creating Digital Forms.
 *
 * Digital Form nodes are wrapped in DigitalForm
 * entity-wrapper objects before being returned.
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
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      $digitalForms = [];
      foreach ($nodes as $node) {
        $digitalForms[] = new DigitalForm($this->entityTypeManager, $node);
      }

      return $digitalForms;
    }
    return [];
  }

  /**
   * Creates a Digital Form node and wraps it.
   *
   * @param array<string,mixed> $fields
   *   The field values.
   *
   * @return \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm|null
   *   A DigitalForm object wrapping the created node, or NULL if not created.
   */
  public function createDigitalForm($fields) {
    try {
      $node = $this->entityTypeManager->getStorage('node')->create([
        'type' => 'digital_form',
      ] + $fields);

      return $this->wrapDigitalForm($node);
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Retrieves a Digital Form node by node id and wraps it.
   *
   * @param bool $nid
   *   The node id.
   *
   * @return \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm|null
   *   A DigitalForm object wrapping the fetched 'digital_form' node,
   *   or NULL if not found.
   */
  public function getDigitalForm($nid) {
    $node = $this->entityTypeManager->getStorage('node')->load($nid);

    return $this->wrapDigitalForm($node);
  }

  /**
   * Returns a DigitalForm object from a passed-in Digital Form node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The `digital_form` node to wrap.
   *
   * @return \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm|null
   *   The DigitalForm object wrapping the passed-in $node, or NULL.
   */
  public function wrapDigitalForm($node) {
    if (!$node) {
      return NULL;
    }

    // Only return the node if it is a Digital Form node.
    if ($node->getType() !== 'digital_form') {
      return NULL;
    }

    return new DigitalForm($this->entityTypeManager, $node);
  }

}
