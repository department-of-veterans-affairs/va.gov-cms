<?php

namespace Drupal\va_gov_backend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes Publish Now! requests.
 */
class PublishNowController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    return $instance;
  }

  /**
   * Publish the node now!
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param \Drupal\node\NodeInterface|null $node
   *   The node to publish.
   */
  public function publishNow(Request $request, NodeInterface $node = NULL) {
    return [
      '#type' => 'markup',
      '#markup' => $node->id(),
    ];
  }

}
