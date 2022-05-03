<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\va_gov_build_trigger\Service\BuildRequesterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Handles content release notifications.
 */
class ContentReleaseRequestController extends ControllerBase {

  /**
   * The release state manager.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface
   */
  protected $buildRequester;

  /**
   * The current request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructor for the content release request controller.
   *
   * @param \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface $buildRequester
   *   The build requester service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The current request stack.
   */
  public function __construct(BuildRequesterInterface $buildRequester, RequestStack $requestStack) {
    $this->buildRequester = $buildRequester;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_build_trigger.build_requester'),
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

    $this->buildRequester->requestFrontendBuild($reason);

    return new Response('Build requested.');
  }

}
