<?php

namespace Drupal\va_gov_content_release\Form\Resolver;

use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
use Drupal\va_gov_build_trigger\Form\LocalBuildTriggerForm;
use Drupal\va_gov_build_trigger\Form\TugboatBuildTriggerForm;
use Drupal\va_gov_content_release\Exception\CouldNotDetermineFormException;
use Drupal\va_gov_environment\Service\DiscoveryInterface;

/**
 * The form resolver service.
 *
 * This service is used to determine which form should be used for the current
 * environment. This decouples the form from the environment, allowing the form
 * to be used in multiple environments.
 */
class Resolver implements ResolverInterface {

  /**
   * The environment discovery service.
   *
   * @var \Drupal\va_gov_environment\Service\DiscoveryInterface
   */
  protected $environmentDiscovery;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_environment\Service\DiscoveryInterface $environmentDiscovery
   *   The environment discovery service.
   */
  public function __construct(DiscoveryInterface $environmentDiscovery) {
    $this->environmentDiscovery = $environmentDiscovery;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormClass() : string {
    $environment = $this->environmentDiscovery->getEnvironment();
    return match (TRUE) {
      $environment->isBrd() => BrdBuildTriggerForm::class,
      $environment->isTugboat() => TugboatBuildTriggerForm::class,
      $environment->isLocalDev() => LocalBuildTriggerForm::class,
      default => throw new CouldNotDetermineFormException('Could not determine a valid content release form for environment: ' . $environment->getRawValue()),
    };
  }

}
