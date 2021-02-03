<?php

namespace Drupal\va_gov_build_trigger;

use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;

/**
 * Status of Web Build.
 */
class WebBuildStatus implements WebBuildStatusInterface {

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
  protected const STATE = 'va.web_build_stats';

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
  public function getWebBuildStatus() : bool {
    return (bool) $this->stateProvider->get(static::STATE, FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function enableWebBuildStatus() : void {
    $this->stateProvider->set(static::STATE, TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function disableWebBuildStatus() : void {
    $this->stateProvider->set(static::STATE, FALSE);
  }

  /**
   * Use CMS export.
   *
   * @return bool
   *   Should we use cms export?
   */
  public function useContentExport() : bool {
    return $this->useContentExport;
  }

}
