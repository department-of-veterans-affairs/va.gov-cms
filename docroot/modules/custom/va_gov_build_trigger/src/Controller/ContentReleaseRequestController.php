<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\va_gov_content_release\Request\RequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Handles content release notifications.
 */
class ContentReleaseRequestController extends ControllerBase {

  /**
   * The request service.
   *
   * @var \Drupal\va_gov_content_release\Request\RequestInterface
   */
  protected $requestService;

  /**
   * The current request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructor for the content release request controller.
   *
   * @param \Drupal\va_gov_content_release\Request\RequestInterface $requestService
   *   The build requester service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The current request stack.
   */
  public function __construct(RequestInterface $requestService, RequestStack $requestStack) {
    $this->requestService = $requestService;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_content_release.request'),
      $container->get('request_stack')
    );
  }

  /**
   * Handle a build request from an external system.
   */
  public function requestBuild() {
    $reason = $this->requestStack
      ->getCurrentRequest()
      ->get('reason');

    if (empty($reason)) {
      throw new BadRequestHttpException('Must provide a reason for requesting a frontend build.');
    }

    $this->requestService->submitRequest($reason);

    return new Response('Build requested.');
  }

}
