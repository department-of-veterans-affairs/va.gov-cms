<?php

namespace Drupal\va_gov_content_release\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\va_gov_content_release\FrontendUrl\FrontendUrlInterface;
use Drupal\va_gov_content_release\Status\StatusInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a Content Release Status block.
 *
 * @Block(
 *  id = "content_release_status_block",
 *  admin_label = @Translation("Content Release Status"),
 * )
 */
class ContentReleaseStatusBlock extends BlockBase implements ContainerFactoryPluginInterface {

  const BLOCK_REFRESH_ROUTE = 'va_gov_content_release.status_block_controller_get_block';

  /**
   * The content release status service.
   *
   * @var \Drupal\va_gov_content_release\Status\StatusInterface
   */
  protected $status;

  /**
   * The frontend URL service.
   *
   * @var \Drupal\va_gov_content_release\FrontendUrl\FrontendUrlInterface
   */
  protected $frontendUrl;

  /**
   * The backend base URL.
   *
   * @var string
   */
  protected $backendBaseUrl;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\va_gov_content_release\Status\StatusInterface $status
   *   The content release status service.
   * @param \Drupal\va_gov_content_release\FrontendUrl\FrontendUrlInterface $frontendUrl
   *   The frontend URL service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    StatusInterface $status,
    FrontendUrlInterface $frontendUrl,
    RequestStack $requestStack
  ) {
    $this->configuration = $configuration;
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->status = $status;
    $this->frontendUrl = $frontendUrl;
    $this->backendBaseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('va_gov_content_release.status'),
      $container->get('va_gov_content_release.frontend_url'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = $this->buildStatusBlock();
    $this->attachStatusBlockLibrary($build);
    return $build;
  }

  /**
   * Build the status block.
   *
   * @return array
   *   The status block.
   */
  public function buildStatusBlock(): array {
    $build = [];
    $build['content_release_status_block'] = [
      '#theme' => 'status_report_grouped',
      '#grouped_requirements' => [
        [
          'title' => $this->t('Content release status'),
          'type' => 'content-release-status',
          'items' => $this->getItems(),
        ],
      ],
    ];
    return $build;
  }

  /**
   * Get the status items.
   *
   * @return array
   *   The status items.
   */
  public function getItems(): array {
    $items = [];
    $items['frontend_link'] = $this->buildFrontendLinkItem();
    $items['release_state'] = $this->getReleaseStateItem();
    $items['last_release'] = $this->getLastReleaseItem();
    $items['content_build_version'] = $this->getContentBuildVersionItem();
    if ($this->status->hasAdditionalBuildDetails()) {
      $items = array_merge($items, $this->getAdditionalBuildDetailsItems());
    }
    return $items;
  }

  /**
   * Build and insert the frontend link.
   *
   * @return array
   *   The frontend link item.
   */
  public function buildFrontendLinkItem(): array {
    if (!$this->hasEverCompletedRelease()) {
      return $this->getDefaultFrontendLinkItem();
    }
    $targetUrl = $this->frontendUrl->getBaseUrl();
    $targetUrl = Url::fromUri($targetUrl, ['attributes' => ['target' => '_blank']]);
    return [
      'title' => $this->t('Front end link'),
      'description' => $this->t('See how your content will appear to site visitors on the front end.'),
      'value' => Link::fromTextAndUrl($this->t('View front end'), $targetUrl),
    ];
  }

  /**
   * Has a release completed? Like, ever?
   *
   * @return bool
   *   TRUE if a release has ever completed, FALSE otherwise.
   */
  public function hasEverCompletedRelease() : bool {
    return $this->status->getLastReleaseCompleteTimestamp() !== 0;
  }

  /**
   * Get the default frontend link item.
   *
   * This is used if we've never completed a frontend build and the frontend
   * is not available.
   *
   * @return array
   *   The default frontend link item.
   */
  public function getDefaultFrontendLinkItem() : array {
    return [
      'title' => $this->t('Front end link'),
      'description' => $this->t('Once a release is completed successfully, this section will update with a link to the newly built VA.gov front end.'),
      'value' => $this->t('Front end has not been built yet.'),
    ];
  }

  /**
   * Get the release state item.
   *
   * @return array
   *   The release state item.
   */
  public function getReleaseStateItem(): array {
    return [
      'title' => $this->t('Release state'),
      'value' => $this->status->getHumanReadableCurrentReleaseState(),
    ];
  }

  /**
   * Get the last release item.
   *
   * @return array
   *   The last release item.
   */
  public function getLastReleaseItem(): array {
    return [
      'title' => $this->t('Last release'),
      'value' => $this->status->getLastReleaseCompleteDate(),
    ];
  }

  /**
   * Get additional build details items.
   *
   * @return array
   *   The additional build details items.
   */
  public function getAdditionalBuildDetailsItems(): array {
    $items = [];
    $items['content_build_version'] = $this->getContentBuildVersionItem();
    $items['vets_website_version'] = $this->getVetsWebsiteVersionItem();
    $items['build_log'] = $this->getBuildLogItem();
    return $items;
  }

  /**
   * Get the content build version item.
   *
   * @return array
   *   The content build version item.
   */
  public function getContentBuildVersionItem(): array {
    return [
      'title' => $this->t('content-build version'),
      'value' => $this->status->getContentBuildVersion(),
    ];
  }

  /**
   * Get the vets-website version item.
   *
   * @return array
   *   The vets-website version item.
   */
  public function getVetsWebsiteVersionItem(): array {
    return [
      'title' => $this->t('vets-website version'),
      'value' => $this->status->getVetsWebsiteVersion(),
    ];
  }

  /**
   * Get the build log item.
   *
   * @return array
   *   The build log item.
   */
  public function getBuildLogItem(): array {
    return [
      'title' => $this->t('Build log'),
      'value' => $this->getBuildLogLink(),
    ];
  }

  /**
   * Get the build log link.
   *
   * @return \Drupal\Core\GeneratedLink
   *   The build log link.
   */
  public function getBuildLogLink() {
    $buildLogPath = $this->status->getBuildLogPath();
    $buildLogUri = $this->backendBaseUrl . $buildLogPath;
    $buildLogUrl = Url::fromUri($buildLogUri, ['attributes' => ['target' => '_blank']]);
    return Link::fromTextAndUrl($this->t('View build log'), $buildLogUrl);
  }

  /**
   * Attach the status block library.
   *
   * @param array $build
   *   The render array.
   */
  public function attachStatusBlockLibrary(array &$build) {
    $build['#attached']['library'][] = 'va_gov_content_release/status_block';
    $build['#attached']['drupalSettings']['contentRelease']['statusBlock'] = [
      'blockRefreshPath' => Url::fromRoute(static::BLOCK_REFRESH_ROUTE)->toString(),
    ];
  }

}
