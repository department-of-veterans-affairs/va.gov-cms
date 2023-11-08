<?php

namespace Drupal\va_gov_content_release\Status;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
use Drupal\va_gov_content_release\Frontend\Frontend;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface;
use Drupal\va_gov_content_release\Strategy\Resolver\ResolverInterface;

/**
 * The content release status service.
 *
 * This service provides information about the status of the content release.
 */
class Status implements StatusInterface {

  use StringTranslationTrait;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The release state manager service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface
   */
  protected $releaseStateManager;

  /**
   * The strategy resolver service.
   *
   * @var \Drupal\va_gov_content_release\Strategy\Resolver\ResolverInterface
   */
  protected $strategyResolver;

  /**
   * The frontend version service.
   *
   * @var \Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface
   */
  protected $frontendVersion;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   * @param \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface $releaseStateManager
   *   The release state manager service.
   * @param \Drupal\va_gov_content_release\Strategy\Resolver\ResolverInterface $strategyResolver
   *   The strategy resolver service.
   * @param \Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface $frontendVersion
   *   The frontend version service.
   */
  public function __construct(
    TranslationInterface $stringTranslation,
    DateFormatterInterface $dateFormatter,
    ReleaseStateManagerInterface $releaseStateManager,
    ResolverInterface $strategyResolver,
    FrontendVersionInterface $frontendVersion
  ) {
    $this->stringTranslation = $stringTranslation;
    $this->dateFormatter = $dateFormatter;
    $this->releaseStateManager = $releaseStateManager;
    $this->strategyResolver = $strategyResolver;
    $this->frontendVersion = $frontendVersion;
  }

  /**
   * {@inheritDoc}
   */
  public function getCurrentReleaseState() : string {
    return $this->releaseStateManager->getState();
  }

  /**
   * {@inheritDoc}
   */
  public function getHumanReadableCurrentReleaseState() : string {
    return match ($this->getCurrentReleaseState()) {
      // An enum will make this more beautiful.
      // @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/15556
      ReleaseStateManager::STATE_READY => $this->t('Ready'),
      ReleaseStateManager::STATE_REQUESTED => $this->t('Requested'),
      ReleaseStateManager::STATE_DISPATCHED => $this->t('Dispatched'),
      ReleaseStateManager::STATE_STARTING => $this->t('Starting'),
      ReleaseStateManager::STATE_INPROGRESS => $this->t('In Progress'),
      ReleaseStateManager::STATE_COMPLETE => $this->t('Complete'),
      default => $this->t('Unknown'),
    };
  }

  /**
   * {@inheritDoc}
   */
  public function getLastReleaseCompleteTimestamp() : int {
    return $this->releaseStateManager->getLastReleaseCompleteTimestamp();
  }

  /**
   * {@inheritDoc}
   */
  public function getLastReleaseCompleteDate() : string {
    $timestamp = $this->getLastReleaseCompleteTimestamp();
    if ($timestamp === 0) {
      return $this->t('Never');
    }
    return $this->dateFormatter->format($timestamp, 'standard');
  }

  /**
   * {@inheritDoc}
   */
  public function hasAdditionalBuildDetails() : bool {
    return in_array($this->strategyResolver->getStrategyId(), StatusInterface::ADDITIONAL_BUILD_DETAILS_STRATEGY_IDS);
  }

  /**
   * {@inheritDoc}
   */
  public function getContentBuildVersion() : string {
    return $this->frontendVersion->getVersion(Frontend::ContentBuild);
  }

  /**
   * {@inheritDoc}
   */
  public function getVetsWebsiteVersion() : string {
    return $this->frontendVersion->getVersion(Frontend::VetsWebsite);
  }

  /**
   * {@inheritDoc}
   */
  public function getBuildLogPath() : string {
    return '/sites/default/files/build.txt';
  }

}
