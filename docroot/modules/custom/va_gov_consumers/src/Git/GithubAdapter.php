<?php

namespace Drupal\va_gov_consumers\Git;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error;
use Github\Client;
use Github\Exception\InvalidArgumentException;

/**
 * Access to Github APIs.
 */
class GithubAdapter implements GithubInterface {
  /**
   * Github Client
   *
   * @var \Github\Client
   */
  protected $githubClient;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Repository Path on Github.
   *
   * @var string
   */
  protected $repositoryPath;

  /**
   * Constructor for the GithubAdapter.
   *
   * @param Client $githubClient
   *   A github client which has been authenticated..
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Channel Factory.
   */
  public function __construct(Client $githubClient, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->githubClient = $githubClient;
    $this->logger = $loggerChannelFactory->get('github');

    try {
      if ($token) {
        $this->githubClient->authenticate($token, NULL, Client::AUTH_HTTP_TOKEN);
      }
    }
    catch (InvalidArgumentException $e) {
      $this->logger->error('Invalid Github Token');
      throw new \InvalidArgumentException('Invalid Github Token');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function searchPullRequests(string $search_on, int $count = 10): array {
    $results = [];
    $string = urlencode($search_on);
    $repo = $this->getRepositoryPath();

    try {
      // @todo add per_page (count) parameter when/if KnpLabs/php-github-api supports it.
      $results = $this->githubClient->api('search')->issues("is:pr is:open repo:{$repo} {$string}");
    }
    catch (\Exception $e) {
      $variables = Error::decodeException($e);
      $this->logger->error('%type: @message in %function (line %line of %file).', $variables);
    }

    return $results;
  }

  /**
   * Get the set repository Path
   *
   * @return string
   *   The repository path.
   */
  public function getRepositoryPath(): string {
    return $this->repositoryPath;
  }

  /**
   * Set the repository path.
   *
   * @param string $repositoryPath
   *   The repository path $org/$repo;
   */
  public function setRepositoryPath(string $repositoryPath): void {
    $this->repositoryPath = $repositoryPath;
  }
}
