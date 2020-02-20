<?php


namespace Drupal\va_gov_content_export;


use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class VaGovContentExportServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    $container->removeDefinition('tome_sync.config_event_subscriber');
  }

}
