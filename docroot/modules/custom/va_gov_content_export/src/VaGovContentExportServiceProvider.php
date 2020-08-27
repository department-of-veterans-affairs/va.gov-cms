<?php

namespace Drupal\va_gov_content_export;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * ServiceProvider.
 */
class VaGovContentExportServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Remove tome sync config event subscriber to not trigger a config export.
    $container->removeDefinition('tome_sync.config_event_subscriber');
    // Remove tome syncs field item processor since it removes processed field.
    $container->removeDefinition('serializer.normalizer.field_tome_sync');
  }

}
