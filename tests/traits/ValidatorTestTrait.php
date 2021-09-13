<?php

namespace Traits;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

/**
 * Useful methods for testing constraint validation.
 */
trait ValidatorTestTrait {

  /**
   * Get a field item list.
   *
   * This should normally be an instance of FieldItemListInterface,
   * but the validator's code doesn't care so long as it's iterable.
   *
   * @param \Drupal\Core\Field\FieldItemInterface[] $items
   *   Field items to tuck inside the list.
   *
   * @return array
   *   A list of field items.
   */
  public function getFieldItemList(array $items) {
    return $items;
  }

  /**
   * Get a field item.
   *
   * Because Drupal, this $value will actually look something like:
   *
   *   [
   *      'value' => 'Drupal developers have mixed feelings about _Inception_',
   *      'format' => 'rich_text',
   *   ]
   *
   * rather than being the actual string value.
   *
   * @param array $value
   *   A field value.
   * @param string $type
   *   The field type.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   A list of field items.
   */
  public function getFieldItem(array $value, string $type) {
    $fieldDefinitionProphecy = $this->prophesize(FieldDefinitionInterface::class);
    $fieldDefinitionProphecy->getType()->willReturn($type);
    $fieldDefinition = $fieldDefinitionProphecy->reveal();
    $resultProphecy = $this->prophesize(FieldItemInterface::class);
    $resultProphecy->getFieldDefinition()->willReturn($fieldDefinition);
    $resultProphecy->getValue()->willReturn($value);
    $result = $resultProphecy->reveal();
    return $result;
  }

  /**
   * Prepare a validator object with a mock execution context.
   *
   * The execution context will assert on some methods according to expectation.
   *
   * This operates under the assumption that we're building the constraint and
   * not simply adding a violation directly.  This is necessary for some types
   * of fields for proper attachment.  An alternative approach might be needed
   * when the violation is added using the simpler method.
   *
   * @param \Symfony\Component\Validator\ConstraintValidatorInterface $validator
   *   A constraint validator object.
   * @param bool $willValidate
   *   TRUE if the test string should validate, otherwise FALSE.
   */
  public function prepareValidator(ConstraintValidatorInterface $validator, bool $willValidate) {
    $cvbProphecy = $this->prophesize(ConstraintViolationBuilderInterface::class);
    $addViolationMock = $cvbProphecy->addViolation();
    if ($willValidate) {
      $addViolationMock->shouldNotBeCalled();
    }
    else {
      $addViolationMock->shouldBeCalled();
    }
    $cvbProphecy
      ->atPath(Argument::type('string'))
      ->will(function ($string) use ($cvbProphecy) {
        return $cvbProphecy->reveal();
      });
    $executionContextProphecy = $this->prophesize(ExecutionContextInterface::class);
    $executionContextProphecy
      ->buildViolation(Argument::type('string'), Argument::type('array'))
      ->will(function ($string, $array) use ($cvbProphecy) {
        return $cvbProphecy->reveal();
      });
    $executionContext = $executionContextProphecy->reveal();
    $validator->setContext($executionContext);
  }

}
