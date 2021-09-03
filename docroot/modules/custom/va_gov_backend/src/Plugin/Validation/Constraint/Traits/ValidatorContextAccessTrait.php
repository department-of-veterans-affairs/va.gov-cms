<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Provides direct access to the validator's execution context.
 *
 * This can ease automated testing.
 */
trait ValidatorContextAccessTrait {

  /**
   * Set the execution context directly.
   *
   * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
   *   An execution context.
   */
  public function setContext(ExecutionContextInterface $context): void {
    $this->context = $context;
  }

  /**
   * Retrieve the execution context.
   *
   * @return \Symfony\Component\Validator\Context\ExecutionContextInterface
   *   The validator's execution context.
   */
  public function getContext(): ExecutionContextInterface {
    return $this->context;
  }

}
