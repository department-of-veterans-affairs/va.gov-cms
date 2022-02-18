<?php

namespace Drupal\va_gov_consumers\Git;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error;
use Github\Client;

/**
 * Access to Github APIs.
 */
class GithubAdapter implements GithubInterface {

  /**
   * Github Client.
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
   * @param \Github\Client $githubClient
   *   A github client which has been authenticated..
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Channel Factory.
   */
  public function __construct(Client $githubClient, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->githubClient = $githubClient;
    $this->logger = $loggerChannelFactory->get('github');
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
      $results = $this->githubClient->search()->issues("is:pr is:open repo:{$repo} {$string}");
    }
    catch (\Exception $e) {
      $variables = Error::decodeException($e);
      $this->logger->error('%type: @message in %function (line %line of %file).', $variables);
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function triggerWorkflow(string $action_name, string $ref, array $params = []): void {
    [$user, $repo] = explode('/', $this->repositoryPath);

    // Exceptions are intentionally *not* caught here - the caller should be
    // able to catch them.
    $this->githubClient->repositories()->workflows()->dispatches(
        $user,
        $repo,
        $action_name,
        $ref,
        $params
      );
  }

  /**
   * {@inheritdoc}
   */
  public function listWorkflowRuns(string $action_name, array $params = []): array {
    [$user, $repo] = explode('/', $this->repositoryPath);

    // Exceptions are intentionally *not* caught here - the caller should be
    // able to catch them.
    return $this->githubClient->repositories()->workflowRuns()->listRuns(
      $user,
      $repo,
      $action_name,
      $params
    );

  }

  /**
   * Get the repository path.
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
   *   The repository path as $org/$repo .
   */
  public function setRepositoryPath(string $repositoryPath): void {
    $this->repositoryPath = $repositoryPath;
  }

}
