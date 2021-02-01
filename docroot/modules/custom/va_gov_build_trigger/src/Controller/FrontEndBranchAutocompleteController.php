<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Error;
use Drupal\va_gov_consumers\Git\Git;
use Drupal\va_gov_consumers\Git\GithubInterface;
use Drupal\va_gov_consumers\Git\GitInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for front end branch autocomplete form element.
 */
class FrontEndBranchAutocompleteController extends ControllerBase {

  /**
   * Github Client.
   *
   * @var \Drupal\va_gov_consumers\Git\GithubInterface
   */
  protected $githubClient;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Git repository class.
   *
   * @var \Drupal\va_gov_consumers\Git\GitInterface
   */
  private $git;

  /**
   * {@inheritdoc}
   */
  public function __construct(GithubInterface $githubClient, GitInterface $git, LoggerChannelFactoryInterface $logger) {
    $this->githubClient = $githubClient;
    $this->logger = $logger->get('va_gov_build_trigger');
    $this->git = $git;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov.consumers.github.factory')->get(
        'department-of-veterans-affairs/vets-website',
        'va_cms_bot_github_auth_token'
      ),
      Git::get($container->get('va_gov.build_trigger.web_build_command_builder')->getPathToWebRoot()),
      $container->get('logger.factory')
    );
  }

  /**
   * Handler for autocomplete request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param int $count
   *   Number of results to return.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The json response object.
   */
  public function handleAutocomplete(Request $request, $count) : Response {
    $results = [];

    if ($input = $request->query->get('q')) {
      $string = mb_strtolower($input);
      $results = $this->getMatchingRefs($string, $count);
    }

    return JsonResponse::create($results);
  }

  /**
   * Get branch and PR git references matching the given search string.
   *
   * @param string $string
   *   Search string.
   * @param int $count
   *   Number of references to return.
   *
   * @return string[]
   *   Array of labeled git references
   */
  private function getMatchingRefs(string $string, int $count) : array {
    // @todo parallelize with https://github.com/spatie/async?
    $results = [];

    $individual_count = (int) ($count / 2);

    $branches = $this->searchFrontEndBranches($string, $individual_count);
    for ($i = 0; $i < $individual_count; $i++) {
      if (!empty($branches[$i])) {
        $results[] = [
          'label' => "BRANCH {$branches[$i]}",
          'value' => "BRANCH {$branches[$i]} ({$branches[$i]})",
        ];
      }
    }

    $frontEndPrs = $this->searchFrontEndPrs($string, $individual_count);
    for ($i = 0; $i < $individual_count; $i++) {
      if (!empty($frontEndPrs['items'][$i])) {
        $item = $frontEndPrs['items'][$i];
        $results[] = [
          'label' => "PR {$item['number']} ({$item['title']})",
          'value' => "PR {$item['number']} - {$item['title']} ({$item['number']})",
        ];
      }
    }

    return $results;
  }

  /**
   * Return front end branch names matching the given string.
   *
   * @param string $string
   *   Search string.
   * @param int $count
   *   Number of branch names to return.
   *
   * @return string[]
   *   Array of branch names.
   */
  private function searchFrontEndBranches(string $string, int $count) : array {
    return $this->git->searchBranches($string, $count);
  }

  /**
   * Search Front End PRs.
   *
   * @param string $string
   *   Search string.
   * @param int $count
   *   Number of PRs to return.
   *
   * @return string[]
   *   Array of PRs
   */
  private function searchFrontEndPrs(string $string, int $count = 20) {
    $results = [];

    try {
      $results = $this->githubClient->searchPullRequests($string, $count);
    }
    catch (\Exception $e) {
      $variables = Error::decodeException($e);
      $this->logger->error('%type: @message in %function (line %line of %file).', $variables);
    }

    return $results;
  }

}
