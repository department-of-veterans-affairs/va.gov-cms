<?php

namespace Drupal\va_gov_form_builder\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueField constraint.
 */
class UniqueFieldValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The entity on which validation is taking place.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  private $entity;


  /**
   * The field definition for the field on which validation is taking place.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface
   */
  private $fieldDefinition;

  /**
   * The value of the field that is being validated.
   *
   * @var mixed
   */
  private $fieldValue;

  /**
   * Constructs a UniqueFieldConstraintValidator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Type Manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns the bundle type of the entity being validated.
   */
  private function getBundleType() {
    return $this->entity->bundle();
  }

  /**
   * Returns the bundle label of the entity being validated.
   */
  private function getBundleLabel() {
    return $this->entityTypeManager->getStorage('node_type')->load($this->getBundleType())->label();
  }

  /**
   * Returns the name of the field being validated.
   */
  private function getFieldName() {
    return $this->fieldDefinition->getName();
  }

  /**
   * Returns the label of the field being validated.
   */
  private function getFieldLabel() {
    return $this->fieldDefinition->getLabel();
  }

  /**
   * Determines if the entity is unique based on the specific field.
   *
   * @return bool
   *   TRUE if the field is unique, FALSE otherwise.
   */
  private function isUnique() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();

    // accessCheck FALSE to ensure all nodes of type `$nodeType` are checked,
    // even if editor does not have access.
    $query->accessCheck(FALSE)
      ->condition('type', $this->getBundleType())
      ->condition($this->getFieldName(), $this->fieldValue);

    // If there's a current node in question,
    // exclude the current node from the query.
    if (!$this->entity->isNew()) {
      $query->condition('nid', $this->entity->id(), '!=');
    }

    $results = $query->execute();

    // If the query returns no results, the field value is unique.
    return empty($results);
  }

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    /** @var \Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueField $constraint */
    if (!$item instanceof FieldItemListInterface) {
      throw new \InvalidArgumentException(
        sprintf(
          'Expected FieldItemListInterface, but got %s.',
          is_object($item) ? get_class($item) : gettype($item),
        )
      );
    }

    $this->entity = $item->getEntity();
    $this->fieldDefinition = $item->getFieldDefinition();
    $this->fieldValue = $item->value;

    if (!$this->isUnique()) {
      $this->context->buildViolation($constraint->message, [
        ':bundle_label' => $this->getBundleLabel(),
        ':field_label' => $this->getFieldLabel(),
        ':field_value' => $this->fieldValue,
      ])->addViolation();
    }
  }

}
