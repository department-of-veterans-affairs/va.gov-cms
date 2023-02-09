<?php

namespace Drupal\va_gov_lovell\Variables;

use Drupal\preprocess_event_dispatcher\Variables\AbstractEventVariables;

use Drupal\Core\Entity\EntityInterface;

/**
 * Wrapper class for breadcrumb preprocess event variables.
 */
class BreadcrumbEventVariables extends AbstractEventVariables {

  /**
   * Get the breadcrumb.
   *
   * @return array
   *   The breadcrumb.
   */
  public function getBreadcrumb(): array {
    return $this->variables['breadcrumb'];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(): EntityInterface {
    return $this->getBreadcrumb();
  }

}
