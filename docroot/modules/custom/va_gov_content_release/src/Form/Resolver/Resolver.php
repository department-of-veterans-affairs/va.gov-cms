<?php

namespace Drupal\va_gov_content_release\Form\Resolver;

use Drupal\va_gov_content_release\Exception\CouldNotDetermineFormException;
use Drupal\va_gov_content_release\Form\GitForm;
use Drupal\va_gov_content_release\Form\SimpleForm;
use Drupal\va_gov_environment\Discovery\DiscoveryInterface;

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
   * @var \Drupal\va_gov_environment\Discovery\DiscoveryInterface
   */
  protected $environmentDiscovery;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_environment\Discovery\DiscoveryInterface $environmentDiscovery
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
      $environment->isBrd() => SimpleForm::class,
      $environment->isTugboat() => GitForm::class,
      $environment->isLocalDev() => GitForm::class,
      default => throw new CouldNotDetermineFormException('Could not determine a valid content release form for environment: ' . $environment->getRawValue()),
    };
  }

}
