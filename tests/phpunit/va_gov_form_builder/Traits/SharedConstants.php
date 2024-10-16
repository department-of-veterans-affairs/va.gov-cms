<?php

namespace tests\phpunit\va_gov_form_builder\Traits;

/**
 * Provides a trait for some constants shared between test classes.
 *
 * Since constants in traits are not supported in PHP versions < 8.2,
 * we write some static methods that return values instead.
 */
trait SharedConstants {

  /**
   * A unique VA form number.
   *
   * @return string
   *   A string that we know will be unique among field_va_form_number
   *   values on Digital Form nodes.
   */
  public static function getUniqueVaFormNumber() {
    return 'unique_va_form_number_!@#$%^&*()';
  }

}
