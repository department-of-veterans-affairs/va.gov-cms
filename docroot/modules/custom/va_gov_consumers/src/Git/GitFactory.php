<?php

namespace Drupal\va_gov_consumers\Git;

use Drupal\Core\Site\Settings;
use Gitonomy\Git\Repository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Generate a Repository class.
 */
class GitFactory implements ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * App Root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * Path to Web root.
   *
   * @var string
   */
  protected $webRoot;

  /**
   * GitFactory constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   */
  public function __construct(Settings $settings) {
    $this->appRoot = $settings->get('va_gov_app_root', '');
    $this->webRoot = $settings->get('va_gov_web_root', '');
  }

  /**
   * Get a Repository Class.
   *
   * @param string $repositoryRoot
   *   The path to the repository root.
   *
   * @return \Drupal\va_gov_consumers\Git\GitInterface
   *   The Git Repository class.
   */
  public function get(string $repositoryRoot) : GitInterface {
    $repository = new Repository($repositoryRoot);
    return Git::get($repository);
  }

  /**
   * Get the Repository class for the Web Root.
   *
   * @return \Drupal\va_gov_consumers\Git\GitInterface
   *   The Git object.
   */
  public function getWebRepository() : GitInterface {
    return $this->get($this->webRoot);
  }

  /**
   * Get the Repository class for the App Root.
   *
   * @return \Drupal\va_gov_consumers\Git\GitInterface
   *   The Git Object.
   */
  public function getAppRepository() : GitInterface {
    return $this->get($this->appRoot);
  }

}
