<?php

namespace Drupal\va_gov_lovell\Variables;

use Drupal\preprocess_event_dispatcher\Variables\AbstractEventVariables;

use Drupal\Core\Entity\EntityInterface;

/**
 * Wrapper class for menu preprocess event variables.
 */
class MenuEventVariables extends AbstractEventVariables {

  /**
   * Get the menu.
   *
   * @return array
   *   The menu.
   */
  public function &getMenu(): array {
    return $this->getByReference('items');
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(): EntityInterface {
    return $this->getMenu();
  }

}
