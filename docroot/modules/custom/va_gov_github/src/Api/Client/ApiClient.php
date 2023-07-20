<?php

namespace Drupal\va_gov_github\Api\Client;

use Drupal\va_gov_github\Exception\InvalidApiTokenException;

/**
 * A service that provides access to the Github API.
 *
 * This is primarily used for triggering actions and repository dispatches.
 */
class ApiClient implements ApiClientInterface {

  /**
   * The GitHub repository owner, e.g. 'department-of-veterans-affairs'.
   *
   * @var string
   */
  protected $owner;

  /**
   * The GitHub repository name, e.g. 'va.gov-cms'.
   *
   * @var string
   */
  protected $repository;

  /**
   * The GitHub token used to authenticate requests.
   *
   * @var string
   */
  protected $token;

  /**
   * The constructor.
   *
   * @param string $owner
   *   The GitHub repository owner.
   * @param string $repository
   *   The GitHub repository name.
   * @param string $token
   *   The GitHub API token.
   *
   * @throws \Drupal\va_gov_github\Exception\InvalidApiTokenException
   *   If the GitHub API token is provided, but is invalid.
   */
  public function __construct(string $owner, string $repository, string $token = NULL) {
    $this->owner = $owner;
    $this->repository = $repository;
    $this->token = $token;
    if ($this->token && !$this->validateToken($this->token)) {
      throw new InvalidApiTokenException('The GitHub API token is invalid.');
    }
  }

}
