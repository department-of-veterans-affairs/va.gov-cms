<?php

namespace Drupal\va_gov_flags;

use Drupal\feature_toggle\FeatureManager;
use Drupal\feature_toggle\FeatureStatus;

/**
 * Build out the Data object.
 */
class FeatureFlagDataBuilder implements FeatureFlagDataBuilderInterface {
  /**
   * Feature Status service.
   *
   * @var \Drupal\feature_toggle\FeatureStatus
   */
  protected $featureStatus;

  /**
   * FeatureManager service.
   *
   * @var \Drupal\feature_toggle\FeatureManager
   */
  protected $featureManager;

  /**
   * ExportFeature constructor.
   *
   * @param \Drupal\feature_toggle\FeatureStatus $featureStatus
   *   Feature Status.
   * @param \Drupal\feature_toggle\FeatureManager $featureManager
   *   Features Config.
   */
  public function __construct(FeatureStatus $featureStatus, FeatureManager $featureManager) {
    $this->featureStatus = $featureStatus;
    $this->featureManager = $featureManager;
  }

  /**
   * {@inheritDoc}
   */
  public function buildData() : array {
    return [
      'data' => $this->getFeatures(),
      'method' => 'GET',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFeatures() : array {
    $flag_toggle = [];
    foreach ($this->featureManager->getFeatures() as $feature) {
      $flag_toggle[$feature->label()] = $this->featureStatus->getStatus($feature->name()) ?? FALSE;
    }

    return $flag_toggle;
  }

}
