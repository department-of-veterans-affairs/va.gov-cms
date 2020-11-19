<?php

namespace Drupal\va_gov_flags\Controller;

use Drupal\va_gov_flags\FeatureFlagDataBuilder;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Implementing our JSON api.
 */
class VaGovFlagsController implements ContainerInjectionInterface {

  /**
   * FeatureFlagBuilder service.
   *
   * @var \Drupal\va_gov_flags\FeatureFlagDataBuilder
   */
  protected $featureFlagDataBuilder;

  /**
   * VaGovFlagsController constructor.
   *
   * @param \Drupal\va_gov_flags\FeatureFlagDataBuilder $featureFlagDataBuilder
   *   The feature flag builder.
   */
  public function __construct(FeatureFlagDataBuilder $featureFlagDataBuilder) {
    $this->featureFlagDataBuilder = $featureFlagDataBuilder;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov.gov_flags.feature_flag_builder')
    );
  }

  /**
   * Callback for the API.
   */
  public function renderApi() {
    return JsonResponse::create(
      $this->featureFlagDataBuilder->buildData()
    );
  }

}
