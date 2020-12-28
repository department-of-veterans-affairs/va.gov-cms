<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Exception;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for front end branch autocomplete form element.
 */
class FrontEndBranchAutocompleteController extends ControllerBase {

  /**
   * Guzzle\Client instance.
   *
   * @var \Guzzle\Client
   */
  protected $httpClient;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(Client $http_client, LoggerChannelFactoryInterface $logger) {
    $this->httpClient = $http_client;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('logger.factory')
    );
  }

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request, $field_name, $count) {
    $results = [];

    if ($input = $request->query->get('q')) {
      $string = Unicode::strtolower($input);

      $frontEndPrs = $this->getFrontEndBranches($string, $count);

      for ($i = 0; $i < $count; $i++) {
        if (!empty($frontEndPrs->items[$i])) {
          $item = $frontEndPrs->items[$i];
          $results[] = [
            'label' => "PR {$item->number} ({$item->title})",
            'value' => "PR {$item->number} - {$item->title} ($item->number)",
          ];
        }
      }
    }

    return new JsonResponse($results);
  }

  /**
   * Search Front End Branches.
   *
   * @return array
   *   Array of results
   */
  private function getFrontEndBranches($string, $count) {
    $results = [];
    $repo = 'department-of-veterans-affairs/vets-website';
    $string = urlencode($string);

    try {
      $request_options = [
        'headers' => [
          'Accept' => 'application/vnd.github.v3+json',
        ],
        'query' => [
          'per_page' => $count,
          'q' => "is:pr is:open repo:{$repo} {$string}",
        ],
      ];

      if ($gh_token = getenv('GITHUB_TOKEN')) {
        $request_options['headers']['Authorization'] = "token {$gh_token}";
      }

      $request = $this->httpClient->get(
        'https://api.github.com/search/issues',
        $request_options
      );

      $results = json_decode($request->getBody());
    }
    catch (Exception $e) {
      $variables = Error::decodeException($exception);
      $this->logger('va_gov_build_trigger')->error('%type: @message in %function (line %line of %file).', $variables);
    }

    return $results;
  }

}
