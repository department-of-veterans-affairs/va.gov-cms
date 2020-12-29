<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Github\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for front end branch autocomplete form element.
 */
class FrontEndBranchAutocompleteController extends ControllerBase {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(LoggerChannelFactoryInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')
    );
  }

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request, $field_name, $count) {
    $results = [];

    if ($input = $request->query->get('q')) {
      $string = mb_strtolower($input);
      $results = $this->getMatchingRefs($string, $count);
    }

    return new JsonResponse($results);
  }

  private function getMatchingRefs($string, $count) {
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

  private function searchFrontEndBranches($string, $count) {
    // @todo Cache these results for a little while.
    // @fixme get root dir.
    $branches = explode(PHP_EOL, shell_exec('cd /app/web && git ls-remote --heads origin | cut -f2 | sed "s#refs/heads/##" '));
    $matches = array_filter($branches, function ($branch_name) use ($string) {
      return stristr($branch_name, $string) !== FALSE;
    });

    return array_slice(array_values($matches), 0, $count);
  }

  /**
   * Search Front End PRs.
   *
   * @return array
   *   Array of results
   */
  private function searchFrontEndPrs($string, $count) {
    $results = [];
    $repo = 'department-of-veterans-affairs/vets-website';
    $string = urlencode($string);

    try {
      $github_client = new Client();

      if ($gh_token = getenv('GITHUB_TOKEN')) {
        $github_client->authenticate($gh_token, NULL, Client::AUTH_HTTP_TOKEN);
      }

      // @todo add count parameter when/if KnpLabs/php-github-api supports it.
      $results = $github_client->api('search')->issues("is:pr is:open repo:{$repo} {$string}");
    }
    catch (\Exception $e) {
      $variables = Error::decodeException($exception);
      $this->logger('va_gov_build_trigger')->error('%type: @message in %function (line %line of %file).', $variables);
    }

    return $results;
  }

}
