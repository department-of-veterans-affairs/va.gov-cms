<?php

namespace Drupal\va_gov_content_release\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\va_gov_content_release\Frontend\Frontend;
use Drupal\va_gov_content_release\FrontendVersionSearch\FrontendVersionSearchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for front end branch autocomplete form element.
 */
class FrontendVersionAutocompleteController extends ControllerBase {

  /**
   * Frontend version search service.
   *
   * @var \Drupal\va_gov_content_release\FrontendVersionSearch\FrontendVersionSearchInterface
   */
  protected $frontendVersionSearch;
  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    FrontendVersionSearchInterface $frontendVersionSearch,
    LoggerChannelFactoryInterface $logger
  ) {
    $this->frontendVersionSearch = $frontendVersionSearch;
    $this->logger = $logger->get('va_gov_content_release');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_content_release.frontend_version_search'),
      $container->get('logger.factory')
    );
  }

  /**
   * Handler for autocomplete request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $frontend
   *   The frontend type.
   * @param int $count
   *   Number of results to return.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The json response object.
   */
  public function handleAutocomplete(Request $request, string $frontend, int $count) : Response {
    $results = [];
    try {
      $frontend = Frontend::from($frontend);
    }
    catch (\InvalidArgumentException $e) {
      return JsonResponse::create(['error' => 'Invalid frontend type provided.']);
    }
    $input = $request->query->get('q') ?? 'main';
    $query = mb_strtolower($input);
    $results = $this->frontendVersionSearch->getMatchingReferences($frontend, $query, $count);
    return JsonResponse::create($results);
  }

}
