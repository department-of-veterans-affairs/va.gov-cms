<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collects logic related to the "Publish Now" button.
 */
class PublishNow implements PublishNowInterface {

  /**
   * The route matching interface.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestInterface
   */
  protected $currentRequest;

  /**
   * Constructor.
   */
  public function __construct(
    ResettableStackedRouteMatchInterface $routeMatch,
    RequestStack $requestStack
  ) {
    $this->routeMatch = $routeMatch;
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * Get the publish URL.
   */
  public function getUrl(NodeInterface $node): string {
    $url = Url::fromRoute('va_gov_backend.publish_now', [
      'node' => $node->id(),
    ]);
    return $url->toString();
  }

  /**
   * {@inheritDoc}
   */
  public function shouldDisplayButton(NodeInterface $node) : bool {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getButtonMarkup(NodeInterface $node) : string {
    return '<a class="button button--primary js-form-submit form-submit node-preview-button" target="_blank" href="' . $this->getUrl($node) . '">Publish Now</a>';
  }

}
