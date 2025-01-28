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

  /**
   * Retrieves a Digital Form node by node id.
   *
   * @param bool $nid
   *   The node id.
   *
   * @return \Drupal\node\NodeInterface|null
   *   A node object of type 'digital_form', or NULL if not found.
   */
  public function getDigitalForm($nid) {
    return $this->entityTypeManager->getStorage('node')->load($nid);
  }

  /**
   * Determines if a Digital Form node has a chapter of a given type.
   *
   * If the node has a chapter (paragraph) of the given type, returns TRUE.
   * Otherwise, returns FALSE.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Digital Form node.
   * @param string $type
   *   The chapter (paragraph) type.
   *
   * @return bool
   *   TRUE if the chapter exists; FALSE if the chapter
   *   does not exist or the node does not exist.
   */
  public function digitalFormHasChapterOfType($node, $type) {
    if (empty($node)) {
      return FALSE;
    }

    if ($node->hasField('field_chapters') && !$node->get('field_chapters')->isEmpty()) {
      $chapters = $node->get('field_chapters')->getValue();

      foreach ($chapters as $chapter) {
        if (isset($chapter['target_id'])) {
          $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($chapter['target_id']);

          if ($paragraph) {
            if ($paragraph->bundle() === $type) {
              return TRUE;
            }
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * Returns the status of a step on the Digital Form node.
   *
   * Completeness of the step varies by step, and is documented
   * in the function body.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Digital Form node.
   * @param string $stepName
   *   The step name of the step in question.
   *
   * @return 'complete'|'incomplete'
   *   Returns 'complete' if step is complete.
   *   Returns 'incomplete if step is incomplete
   *   or if the node does not exist.
   */
  public function getDigitalFormStepStatus($node, $stepName) {
    if (empty($node)) {
      return 'incomplete';
    }

    if ($stepName === 'form_info') {
      // If the node exists, this will necessarily be complete.
      return 'complete';
    }

    if ($stepName === 'review_and_sign') {
      // This is added automatically by the Forms Library.
      return 'complete';
    }

    if (in_array($stepName, [
      'intro',
      'confirmation',
    ])) {
      // These haven't been handled yet.
      // Return 'incomplete' for the time being.
      return 'incomplete';
    }

    // Standard steps are complete if a corresponding chapter exists.
    $standardSteps = [
      'your_personal_info' => 'digital_form_your_personal_info',
      'address_info' => 'digital_form_address',
      'contact_info' => 'digital_form_phone_and_email',
    ];
    if (array_key_exists($stepName, $standardSteps)) {
      $paragraphName = $standardSteps[$stepName];
      return $this->digitalFormHasChapterOfType($node, $paragraphName)
        ? 'complete'
        : 'incomplete';
    }
  }

}
