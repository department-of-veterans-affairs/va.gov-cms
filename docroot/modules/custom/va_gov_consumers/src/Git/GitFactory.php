<?php

namespace Drupal\va_gov_consumers\Git;

use Gitonomy\Git\Repository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Generate a Repository class.
 */
class GitFactory implements ContainerAwareInterface {

  use ContainerAwareTrait;

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
    return $this->get(
      $this->container->get('va_gov.build_trigger.web_build_command_builder')->getPathToWebRoot()
    );
  }

  /**
   * Get the Repository class for the App Root.
   *
   * @return \Drupal\va_gov_consumers\Git\GitInterface
   *   The Git Object.
   */
  public function getAppRepository() : GitInterface {
    return $this->get(
      $this->container->get('va_gov.build_trigger.web_build_command_builder')->getAppRoot()
    );
  }

}
