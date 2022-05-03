<?php

namespace Drupal\va_gov_build_trigger\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\va_gov_build_trigger\Service\BuildRequester;

/**
 * Provides a 'ContentReleaseStatusBlock' block.
 *
 * @Block(
 *  id = "content_release_status_block",
 *  admin_label = @Translation("Recent updates"),
 * )
 */
class ContentReleaseStatusBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * State service
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->state = $container->get('state');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $table = [
      '#type' => 'table',
      '#attributes' => ['class' => ['content-release-status-block']],
      '#header' => [
        $this->t('Data'),
        $this->t('Value'),
      ],
    ];

    // @todo present the data that we have available in a nice way
    $table['#rows'] = [
      [
        'current release state',
        $this->state->get('va_gov_build_trigger.release_state', 'ready'),
      ],
      [
        'all valid release states (in order)',
        'ready, requested, dispatched, starting, in progress, complete',
      ],
      [
        'last release ready time',
        $this->state->get('va_gov_build_trigger.last_release_ready', '0'),
      ],
      [
        'last release request time',
        $this->state->get('va_gov_build_trigger.last_release_request', '0'),
      ],
      [
        'last release dispatch time',
        $this->state->get('va_gov_build_trigger.last_release_dispatch', '0'),
      ],
      [
        'last release starting time',
        $this->state->get('va_gov_build_trigger.last_release_starting', '0'),
      ],
      [
        'last release inprogress time',
        $this->state->get('va_gov_build_trigger.last_release_inprogress', '0'),
      ],
      [
        'last release complete time',
        $this->state->get('va_gov_build_trigger.last_release_complete', '0'),
      ],
      [
        'current frontend version selection',
        $this->state->get(BuildRequester::VA_GOV_FRONTEND_VERSION, '[default]'),
      ],
      [
        'last build log',
        '/sites/default/files/build.txt',
      ],
      [
        'current time',
        \Drupal::service('date.formatter')->format(\Drupal::time()->getCurrentTime(), 'standard'),
      ],
      [
        'unformatted time',
        \Drupal::time()->getCurrentTime(),
      ],
    ];

    $build['#attached']['library'][] = 'va_gov_build_trigger/content_release_status_block';
    $build['#attached']['drupalSettings']['contentReleaseStatusBlock'] = [
      'blockRefreshPath' => Url::fromRoute(
        'va_gov_build_trigger.content_release_status_block_controller_get_block'
      )->toString(),
    ];

    $build['content_release_status_block'] = $table;

    return $build;
  }

}
