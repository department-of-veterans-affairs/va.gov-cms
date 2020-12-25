<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Unicode;
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
   * {@inheritdoc}
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
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
          $results[] = [
            'label' => 'PR ' . $frontEndPrs->items[$i]->number . ' (' . $frontEndPrs->items[$i]->title . ')',
            'value' => 'PR ' . $frontEndPrs->items[$i]->number . ' (' . $frontEndPrs->items[$i]->title . ')',
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
      $request = $this->httpClient->get(
        'https://api.github.com/search/issues',
        [
          'headers' => [
            'Accept:' => 'application/vnd.github.v3+json',
          ],
          'query' => [
            'per_page' => $count,
            'q' => "is:pr is:open repo:{$repo} {$string}",
          ],
        ]
      );

      $results = json_decode($request->getBody());
    }
    catch (\Exception $e) {
      watchdog_exception('asdf', $e);
    }

    return $results;
  }

}
