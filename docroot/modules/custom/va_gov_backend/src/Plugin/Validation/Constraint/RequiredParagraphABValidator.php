<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Core\Entity\FieldableEntityInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the RequiredParagraph constraint.
 */
class RequiredParagraphABValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    assert($entity instanceof FieldableEntityInterface, 'Entity should inherit from FieldableEntityInterface.');
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\RequiredParagraphAB $constraint */
    if (!$entity->hasField($constraint->toggle) && !$entity->hasField($constraint->fieldParagraphA) && !$entity->hasField($constraint->fieldParagraphB)) {
      return;
    }
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $panel_enabled = $entity->get($constraint->toggle)->getString();
    $countA = $this->getCountForParagraphField($constraint->fieldParagraphA);
    $countB = $this->getCountForParagraphField($constraint->fieldParagraphB);
    $number = $countA + $countB;
    $paragraphAField = $this->getBaseField($constraint->fieldParagraphA);
    $paragraphBField = $this->getBaseField($constraint->fieldParagraphB);
    $errorPath = $countA ? $paragraphAField : $paragraphBField;
    if ($panel_enabled && $number < $constraint->min && $number > 0) {
      $this->context->buildViolation($constraint->tooFew, [
        '%plurlLabel' => $constraint->pluralLabel,
        '%readable' => $constraint->readable,
        '%min' => $constraint->min,
      ])
        ->atPath($errorPath)
        ->addViolation();
    }
    elseif ($panel_enabled && $number > $constraint->max) {
      $this->context->buildViolation($constraint->tooMany, [
        '%plurlLabel' => $constraint->pluralLabel,
        '%readable' => $constraint->readable,
        '%max' => $constraint->max,
      ])
        ->atPath($errorPath)
        ->addViolation();
    }
    elseif ($panel_enabled && $number === 0) {
      // Adding a violation in this way ensures that it is displayed even if
      // paragraphA and paragraphB have no values.
      $this->context->addViolation($constraint->required, [
        '%min' => $constraint->min,
        '%panelLabel' => $constraint->panelLabel,
        '%readable' => $constraint->readable,
      ]);
    }
  }

  /**
   * Gets the item count from a paragraph field.
   *
   * To target a nested field (a field within a paragraph), specify the $field
   * with a colon ":" between the parent and child field names. Only one level
   * of nesting is supported. eg: field_faq_group:field_faq_items.
   *
   * @param string $field
   *   The field name to get the count from.
   *
   * @return int
   *   The item count for the number of nested items.
   */
  private function getCountForParagraphField(string $field): int {
    $count = 0;
    $entity = $this->context->getRoot()->getEntity();
    if (str_contains($field, ':')) {
      $fields = explode(":", $field);
      if (!empty($fields)) {
        [$outerParagraphField, $innerParagraphField] = $fields;
        if ($entity->hasField($outerParagraphField)) {
          /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $item */
          foreach ($entity->get($outerParagraphField) as $item) {
            /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
            $paragraph = $item->entity;
            if ($paragraph->hasField($innerParagraphField)) {
              $count += $paragraph->get($innerParagraphField)->count();
            }
          }
        }
      }
    }
    else {
      $count = $entity->get($field)->count();
    }
    return $count;
  }

  /**
   * Get a base field from a given paragraph field identifier.
   *
   * Since fields can contain colon's (":") to separate parent:child, this
   * method is used to get the base field.
   *
   * @return string
   *   The base field name.
   */
  private function getBaseField(string $field): string {
    if (str_contains($field, ':')) {
      [$baseField] = explode(":", $field);
      return $baseField;
    }
    else {
      return $field;
    }
  }

}
