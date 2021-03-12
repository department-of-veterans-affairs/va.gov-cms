<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ExclusionTypes.
 */
class ExclusionTypes implements ExclusionTypesInterface {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a new ExclusionTypes object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritDoc}
   */
  public function getExcludedTypes() : array {
    $excluded_types = [];

    if (!empty($this->configFactory->get('exclusion_types_admin.settings')->get('types_to_exclude'))) {
      $excluded_types = $this->configFactory->get('exclusion_types_admin.settings')->get('types_to_exclude');
    }

    return $excluded_types;
  }

  /**
   * {@inheritDoc}
   */
  public function getJson() : string {
    return json_encode($this->getExcludedTypes());
  }

  /**
   * {@inheritDoc}
   */
  public function typeIsExcluded(string $bundle) : bool {
    if (in_array($bundle, $this->getExcludedTypes())) {
      return TRUE;
    }

    return FALSE;
  }

}
