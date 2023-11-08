<?php

namespace Drupal\va_gov_content_release\Controller;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Retrieves the status block.
 */
class StatusBlockController extends ControllerBase {

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
   * Constructs a new ContentReleaseStatusBlockController.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   *   The block manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(BlockManagerInterface $blockManager, RendererInterface $renderer) {
    $this->pluginManagerBlock = $blockManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('renderer')
    );
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
