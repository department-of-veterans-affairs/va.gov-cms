<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Site\Settings;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base Class used for Environment Plugins.
 */
abstract class EnvironmentPluginBase extends PluginBase implements EnvironmentInterface, ContainerFactoryPluginInterface {
  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The filesystem service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $filesystem;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerInterface $logger,
    FileSystemInterface $filesystem
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->filesystem = $filesystem;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('va_gov_build_trigger'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild() : void {
    $this->filesystem->saveData('build plz', 'public://.buildrequest', FileSystemInterface::EXISTS_REPLACE);
    $this->messenger()->addStatus('A request to rebuild the front end has been submitted.');
  }

  /**
   * {@inheritDoc}
   */
  public function contentEditsShouldTriggerFrontendBuild() : bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getWebUrl(): string {
    return Settings::get('va_gov_frontend_url') ?? 'https://www.va.gov';
  }

  /**
   * {@inheritDoc}
   */
  public function shouldDisplayBuildDetails() : bool {
    return FALSE;
  }

}
