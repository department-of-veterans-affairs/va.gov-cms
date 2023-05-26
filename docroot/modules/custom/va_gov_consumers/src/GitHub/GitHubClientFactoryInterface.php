<?php

namespace Drupal\va_gov_consumers\GitHub;

/**
 * A factory service for creating GitHubClient services.
 */
interface GitHubClientFactoryInterface {

  /**
   * Get a new client for the specified repository.
   *
   * @param string $repositoryPath
   *   The path to the repo, e.g. 'department-of-veterans-affairs/va.gov-cms'.
   * @param string $tokenSettingName
   *   The name of the settings variable that store the github token.
   *
   * @return \Drupal\va_gov_consumers\GitHub\GitHubClientInterface
   *   The GitHub client.
   *
   * @throws \InvalidArgumentException
   *   If the repository path or token setting name are empty.
   */
  public function getClient(string $repositoryPath, string $tokenSettingName) : GitHubClientInterface;

}
