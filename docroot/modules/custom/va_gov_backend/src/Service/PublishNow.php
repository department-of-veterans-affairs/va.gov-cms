<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collects logic related to the "Publish Now" button.
 */
class PublishNow implements PublishNowInterface {

  /**
   * The Route Match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The Current User service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The Current Request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestInterface
   */
  protected $request;

  /**
   * The VA.gov URL service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestInterface
   */
  protected $liveUrl;

  /**
   * The environment discovery service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * Constructor.
   */
  public function __construct(
    RouteMatchInterface $routeMatch,
    AccountProxyInterface $currentUser,
    RequestStack $requestStack,
    VaGovUrlInterface $liveUrl,
    EnvironmentDiscovery $environmentDiscovery,
    LinkGeneratorInterface $linkGenerator
  ) {
    $this->routeMatch = $routeMatch;
    $this->currentUser = $currentUser;
    $this->request = $requestStack->getCurrentRequest();
    $this->liveUrl = $liveUrl;
    $this->environmentDiscovery = $environmentDiscovery;
    $this->linkGenerator = $linkGenerator;
  }

  /**
   * Get the publish URL.
   */
  public function getUrl(NodeInterface $node): Url {
    $url = Url::fromRoute('va_gov_backend.publish_now', [
      'node' => $node->id(),
    ]);
    return $url;
  }

  /**
   * {@inheritDoc}
   */
  public function canPublishNode(NodeInterface $node) : bool {
    if ($this->routeMatch->getRouteName() === 'entity.node.edit_form') {
      return FALSE;
    }
    if (!$node->isPublished()) {
      return FALSE;
    }
    if ($node->bundle() !== 'faq_multiple_q_a') {
      return FALSE;
    }
    if (!$this->currentUser->hasPermission('administer content')) {
      return FALSE;
    }
    if ($this->request->getHost() === 'prod.cms.va.gov') {
      return FALSE;
    }
    if (!$this->environmentDiscovery->isBRD()) {
      return FALSE;
    }
    if (!$this->liveUrl->vaGovFrontEndUrlForEntityIsLive($node)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getButtonMarkup(NodeInterface $node) : string {
    $url = $this->getUrl($node);
    $url->setOptions([
      'attributes' => [
        'class' => [
          'button',
          'button--primary',
          'js-form-submit',
          'form-submit',
          'node-publish-button',
        ],
        'target' => [
          '_blank',
        ],
      ],
    ]);
    return $this->linkGenerator->generate('Publish Now', $url);
  }

}
