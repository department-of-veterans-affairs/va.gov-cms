<?php

namespace Drupal\va_gov_consumers\Git;

use Github\Client;
use Github\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Factor class for GithubAdapter.
 */
class GithubFactory implements ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * Get a new instance of \Drupal\va_gov_consumers\Github\GithubAdapter.
   *
   * @param string $repo_path
   *   The repository path $org/$repo.
   * @param string $token_settings_name
   *   The name of the settings variable that store the github token.
   *
   * @return \Drupal\va_gov_consumers\Git\GithubInterface
   *   The github adapter.
   */
  public function get(string $repo_path, string $token_settings_name) : GithubInterface {
    $github = $this->container->get('github.client');

    $token = $this->container->get('settings')->get($token_settings_name, '');

    try {
      if ($token) {
        $github->authenticate($token, NULL, Client::AUTH_HTTP_TOKEN);
      }
    }
    catch (InvalidArgumentException $e) {
      $this->logger->error('Invalid Github Token');
      throw new \InvalidArgumentException('Invalid Github Token');
    }

    $githubAdapter = new GithubAdapter($github, $this->container->get('logger.factory'));
    $githubAdapter->setRepositoryPath($repo_path);

    return $githubAdapter;
  }

}
