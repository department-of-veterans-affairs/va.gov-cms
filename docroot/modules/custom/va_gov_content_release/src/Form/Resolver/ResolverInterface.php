<?php

namespace Drupal\va_gov_content_release\Form\Resolver;

/**
 * An interface for the form resolver service.
 */
interface ResolverInterface {

  /**
   * Retrieve the form class for the current environment.
   */
  public function getFormClass() : string;

}
