<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
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
   * The WebStatus service.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildStatusInterface
   */
  protected $webBuildStatus;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger, WebBuildStatusInterface $webBuildStatus) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->webBuildStatus = $webBuildStatus;
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
      $container->get('va_gov.build_trigger.web_build_status')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFrontEndGitReferenceCheckoutCommand($front_end_git_ref) : string {
    $repo_root = dirname(DRUPAL_ROOT);
    $build_date = time();
    $web_branch = "build-{$front_end_git_ref}-{$build_date}";

    if (is_numeric($front_end_git_ref)) {
      return "cd {$repo_root}/web && git fetch origin pull/{$front_end_git_ref}/head:{$web_branch} && git checkout {$web_branch}";
    }
    elseif ($front_end_git_ref) {
      return "cd {$repo_root}/web && git checkout -b {$web_branch} origin/{$front_end_git_ref}";
    }

    return '';
  }

  /**
   * {@inheritDoc}
   */
  public function getWebUrl(): string {
    return Settings::get('va_gov_frontend_url') ?? 'https://www.va.gov';
  }

}
