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
    // Remove Tome Sync's config event subscriber to not trigger a
    // config export which we don't use for our use case.
    $container->removeDefinition('tome_sync.config_event_subscriber');
    // Remove Tome Sync's field item processor since it removes the
    // `processed` field which must be present in output.
    $container->removeDefinition('serializer.normalizer.field_tome_sync');
  }

}
