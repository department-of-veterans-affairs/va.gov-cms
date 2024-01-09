<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\node\NodeInterface;
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
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\RequiredParagraphAB $constraint */
    if (!$entity instanceof NodeInterface) {
      return;
    }
    if (!$entity->hasField($constraint->toggle) && !$entity->hasField($constraint->fieldParagraphA) && !$entity->hasField($constraint->fieldParagraphB)) {
      return;
    }
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $panel_enabled = $entity->get($constraint->toggle)->getString();
    $countA = $this->getCountForParagraphField($constraint->fieldParagraphA);
    $countB = $this->getCountForParagraphField($constraint->fieldParagraphB);
    $number = $countA + $countB;

    if ($panel_enabled && $number < $constraint->min) {
      $this->context->buildViolation($constraint->tooFew, [
        '%number' => $number,
        '%min' => $constraint->min,
        '%paragraph' => $constraint->readable,
      ])
        ->atPath($constraint->fieldParagraphA)
        ->addViolation();
      $this->context->buildViolation($constraint->tooFew, [
        '%number' => $number,
        '%min' => $constraint->min,
        '%paragraph' => $constraint->readable,
      ])
        ->atPath($constraint->fieldParagraphB)
        ->addViolation();
    }
    elseif ($panel_enabled && $number > $constraint->max) {
      $this->context->buildViolation($constraint->tooMany, [
        '%number' => $number,
        '%max' => $constraint->max,
        '%paragraph' => $constraint->readable,
      ])
        ->atPath($constraint->fieldParagraphA)
        ->addViolation();
      $this->context->buildViolation($constraint->tooMany, [
        '%number' => $number,
        '%max' => $constraint->max,
        '%paragraph' => $constraint->readable,
      ])
        ->atPath($constraint->fieldParagraphB)
        ->addViolation();
    }
    elseif ($panel_enabled && $number === 0) {
      $this->context->addViolation($constraint->required, [
        '%paragraph' => $constraint->readable,
      ]);
      $this->context->buildViolation($constraint->required, [
        '%paragraph' => $constraint->readable,
      ])
        ->atPath($constraint->fieldParagraphA)
        ->addViolation();
      $this->context->buildViolation($constraint->required, [
        '%paragraph' => $constraint->readable,
      ])
        ->atPath($constraint->fieldParagraphB)
        ->addViolation();
    }
  }

  /**
   * Gets the item count from a paragraph field.
   *
   * To target a nested field (a field within a paragraph), specify the $field
   * with a colon ":" between the parent and child field names. Only one level
   * of nesting is supported.
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

}
