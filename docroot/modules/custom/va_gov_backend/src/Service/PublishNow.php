<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collects logic related to the "Publish Now" button.
 */
class PublishNow implements PublishNowInterface {

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
   * Constructor.
   */
  public function __construct(
    AccountProxyInterface $currentUser,
    RequestStack $requestStack,
    VaGovUrlInterface $liveUrl,
    EnvironmentDiscovery $environmentDiscovery
  ) {
    $this->currentUser = $currentUser;
    $this->request = $requestStack->getCurrentRequest();
    $this->liveUrl = $liveUrl;
    $this->environmentDiscovery = $environmentDiscovery;
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
  public function canPublishNode(NodeInterface $node) : bool {
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
      // Commented out for ease of testing on Tugboat.
      // return FALSE;
    }
    if (!$this->liveUrl->vaGovFrontEndUrlForEntityIsLive($node)) {
      // Commented out for ease of testing on Tugboat.
      // return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getButtonMarkup(NodeInterface $node) : string {
    return '<a class="button button--primary js-form-submit form-submit node-preview-button" target="_blank" href="' . $this->getUrl($node) . '">Publish Now</a>';
  }

}
