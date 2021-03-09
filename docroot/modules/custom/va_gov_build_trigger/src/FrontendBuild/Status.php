<?php

namespace Drupal\va_gov_build_trigger\FrontendBuild;

use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;

/**
 * Frontend Build status.
 */
class Status implements StatusInterface {

  public const USE_CMS_EXPORT_SETTING = 'va_gov_use_cms_export';

  /**
   * Use CMS Export.
   *
   * @var bool
   */
  protected $useContentExport;

  /**
   * The state provider.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $stateProvider;

  /**
   * Web Build Status Key.
   *
   * @var string;
   */
  protected const STATE = 'va.web_build_status';

  /**
   * WebBuildStatus constructor.
   *
   * @param \Drupal\Core\State\StateInterface $stateProvider
   *   The drupal state provider.
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   */
  public function __construct(StateInterface $stateProvider, Settings $settings) {
    $this->stateProvider = $stateProvider;
    $this->useContentExport = $settings->get(static::USE_CMS_EXPORT_SETTING, FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function getStatus() : bool {
    return (bool) $this->stateProvider->get(static::STATE, FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function setStatus(bool $status) : void {
    $this->stateProvider->set(static::STATE, $status);
  }

  /**
   * {@inheritDoc}
   */
  public function useContentExport() : bool {
    return $this->useContentExport;
  }

}
