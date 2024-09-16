<?php

namespace Drupal\va_gov_form_builder\Service\FieldValidator;

/**
 * A service used to validate the uniqueness of a node based on a given field.
 */
class UniquenessValidator {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs the validator service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Type Manager.
   */
  public function __construct($entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * The validation function.
   *
   * @param string $nodeType
   *   The type of the node (machine name) to check.
   * @param string $fieldName
   *   The field name to validate.
   * @param mixed $fieldValue
   *   The value of the field to check for uniqueness.
   * @param int|string|null $nid
   *   (optional) The node ID to exclude from the validation, typically used
   *   when validating during a node edit. Defaults to NULL.
   *
   * @return bool
   *   TRUE if the field is unique, FALSE otherwise.
   */
  public function validate($nodeType, $fieldName, $fieldValue, $nid = NULL) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();

    // accessCheck FALSE to ensure all nodes of type `$nodeType` are checked,
    // even if editor does not have access.
    $query->accessCheck(FALSE)
      ->condition('type', $nodeType)
      ->condition($fieldName, $fieldValue);

    // If there's a current node in question,
    // exclude the current node from the query.
    if ($nid) {
      $query->condition('nid', $nid, '!=');
    }

    $results = $query->execute();

    // If the query returns no results, the field value is unique.
    return empty($results);
  }

}
