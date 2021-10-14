<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Retrieves the content release status block.
 */
class ContentReleaseStatusBlockController extends ControllerBase {

  /**
   * Drupal\Core\Block\BlockManagerInterface definition.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $pluginManagerBlock;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->pluginManagerBlock = $container->get('plugin.manager.block');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Get content_release_status_block content.
   *
   * @return string
   *   Rendered block.
   */
  public function getBlock() {
    $block = $this->pluginManagerBlock
      ->createInstance('content_release_status_block')
      ->build();
    $output = $this->renderer->render($block);
    return Response::create($output);
  }

}
