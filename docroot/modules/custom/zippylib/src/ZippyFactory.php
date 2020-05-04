<?php

namespace Drupal\zippylib;

use Alchemy\Zippy\Zippy;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_content_export\TarStrategy;

/**
 * ZippyFactory Service Class.
 */
class ZippyFactory {

  /**
   * Load the Zippy class.
   *
   * @todo Add plugin for Zippy Strategy and Adapters.
   * @see https://zippy.readthedocs.io/en/latest/#add-custom-utility-strategy
   *
   * @return \Alchemy\Zippy\Zippy
   *   The Zippy file.
   */
  public function get() : Zippy {
    $zippy = Zippy::load();

    $zippy->addStrategy(new TarStrategy());

    $adapter_settings = $this->getAdapterSettings();
    if (!$adapter_settings) {
      return $zippy;
    }

    foreach ($adapter_settings as $adapter => $setting) {
      $zippy->adapters[$adapter] = $setting;
    }

    return $zippy;
  }

  /**
   * Get an array of adapter settings.
   *
   * @return array
   *   An array of adapter overrides.
   */
  protected function getAdapterSettings() : array {
    $zippy_settings = Settings::get('zippy');
    $adapter_settings = $zippy_settings['adapter'] ?? [];
    $adapter_settings_keys = $this->getAdapterSettingKeys();
    $adapter_keys_filter = array_combine(
      $adapter_settings_keys,
      $adapter_settings_keys
    );

    return array_filter($adapter_settings, function ($k) use ($adapter_keys_filter) {
      return isset($adapter_keys_filter[$k]);
    });
  }

  /**
   * Get valid adapter keys.
   *
   * @return array
   *   An array of possible adapter settings.
   */
  protected function getAdapterSettingKeys() : array {
    return [
      'gnu-tar.inflator',
      'gnu-tar.deflator',
      'bsd-tar.inflator',
      'bsd-tar.deflator',
      'zip.inflator',
      'zip.deflator',
    ];
  }

}
