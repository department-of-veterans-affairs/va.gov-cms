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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->pluginManagerBlock = $container->get('plugin.manager.block');
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
    $output = render($block);
    return Response::create($output);
  }

}
