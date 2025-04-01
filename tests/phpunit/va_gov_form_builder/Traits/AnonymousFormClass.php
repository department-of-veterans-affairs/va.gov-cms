<?php

namespace tests\phpunit\va_gov_form_builder\Traits;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a trait for some shared methods for anonymous form classes.
 */
trait AnonymousFormClass {

  /**
   * Form classes must implement `getFormId`.
   *
   * Here, we just return a test id.
   */
  public function getFormId() {
    return 'test_form';
  }

  /**
   * Form classes must implement `submitForm`.
   *
   * Here, we just return (perform no action).
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Override the translate function.
   *
   * Override to simply freturn the passed-in string,
   * thus negating the need for the service container here.
   *
   * Note: Without this, error thrown:
   * "\Drupal::$container is not initialized yet."
   */
  public function t($s, array $args = [], array $options = []) {
    return $s;
  }

}
