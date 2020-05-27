<?php


namespace Drupal\skip_all_paths;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

/**
 * A Kernel to use with site Alerts.
 */
class SettingsCacheChecker {

  /**
   * @var RemoveCacheFromSettings
   */
  protected $removeCacheFromSettings;

  /**
   * @var array
   */
  protected $settings;

  /**
   * SiteAlertKernel constructor.
   *
   * @param \Drupal\skip_all_paths\RemoveCacheFromSettings $removeCacheFromSettings
   * @param array $settings
   */
  public function __construct(RemoveCacheFromSettings $removeCacheFromSettings, array $settings) {
    $this->removeCacheFromSettings = $removeCacheFromSettings;
    $this->settings = $settings;
  }

  public static function create(array $settings): SettingsCacheChecker {
    return new static(
      new RemoveCacheFromSettings(),
      $settings
    );
  }

  /**
   * Check if we are in Maintenance Mode.
   * This is set by manually including a maintenance mode settings file during deploy.
   *
   * Maintenance mode is normally set in the database which we don't have setup at this point.
   *
   * @return bool
   */
  public function isInMaintenanceMode() : bool {
    return $this->settings['skip_all_caches_enabled'] ?? FALSE;
  }

  /**
   * Update the Settings to remove Cache.
   *
   * @return array
   *   The new settings array.
   */
  public function getUpdatedSettings() : array {
    return $this->removeCacheFromSettings->updateSettingsArray($this->settings);
  }

  /**
   * Should the current path check to skip all caches.
   *
   * @return bool
   *   Should we remove cache for a path.
   */
  public function shouldSkipAllCache(Request $request) : bool {
    if (!$this->$this->isInMaintenanceMode()) {
      return FALSE;
    }

    if (PHP_SAPI === 'cli') {
      return FALSE;
    }

    $path = $request->getPathInfo();
    $allowed_paths = Settings::get('skip_all_caches_for_paths', []);
    $trimmed_path = ltrim($path, '/');
    if (in_array($trimmed_path, $allowed_paths)) {
      return TRUE;
    }

    return FALSE;
  }
}
