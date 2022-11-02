<?php

namespace Drupal\va_gov_workflow_assignments\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\va_gov_backend\Service\ExclusionTypesInterface;
use Drupal\va_gov_backend\Service\VaGovUrlInterface;
use Drupal\va_gov_lovell\LovellOps;
use Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepository;
use Drupal\va_gov_workflow_assignments\Service\SectionHierarchyBreadcrumbInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block of Meta information for entities.
 *
 * @Block(
 *   id = "va_gov_workflow_assignments_meta",
 *   admin_label = @Translation("Entity Meta Display"),
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Node"),
 *       required = FALSE,
 *     )
 *   }
 * )
 */
class EntityMetaDisplay extends BlockBase implements ContainerFactoryPluginInterface {

  const BLOCK_ITEM_KEY__CONTENT_TYPE = 'Content Type';
  const BLOCK_ITEM_KEY__OWNER = 'Owner';
  const BLOCK_ITEM_KEY__URLS = 'VA.gov URL';

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Exclusion Types service.
   *
   * @var \Drupal\va_gov_backend\Service\ExclusionTypesInterface
   */
  protected $exclusionTypes;

  /**
   * The va.gov URL service.
   *
   * @var \Drupal\va_gov_backend\Service\VaGovUrlInterface
   */
  protected $vaGovUrl;

  /**
   * The Editorial Workflow service.
   *
   * @var \Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepository
   */
  protected $editorialWorkflowContentRepository;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The section hierarchy breadcrumb service.
   *
   * @var \Drupal\va_gov_workflow_assignments\Service\SectionHierarchyBreadcrumbInterface
   */
  protected $sectionHierarchyBreadcrumb;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    ExclusionTypesInterface $exclusionTypes,
    VaGovUrlInterface $vaGovUrl,
    EditorialWorkflowContentRepository $editorialWorkflowContentRepository,
    RendererInterface $renderer,
    SectionHierarchyBreadcrumbInterface $sectionHierarchyBreadcrumb
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->exclusionTypes = $exclusionTypes;
    $this->vaGovUrl = $vaGovUrl;
    $this->editorialWorkflowContentRepository = $editorialWorkflowContentRepository;
    $this->renderer = $renderer;
    $this->sectionHierarchyBreadcrumb = $sectionHierarchyBreadcrumb;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('va_gov_backend.exclusion_types'),
      $container->get('va_gov_backend.va_gov_url'),
      $container->get('va_gov_workflow_assignments.editorial_workflow'),
      $container->get('renderer'),
      $container->get('va_gov_workflow_assignments.section_hierarchy_breadcrumb')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    if (!$node || !($node instanceof NodeInterface)) {
      return;
    }
    return $this->buildBlock($node);
  }

  /**
   * Build a block for the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A valid, saved node.
   *
   * @return array
   *   A render array for the content of the block.
   */
  public function buildBlock(NodeInterface $node): array {
    $block = [];
    $blockItems = [];
    $blockItems[static::BLOCK_ITEM_KEY__CONTENT_TYPE] = $node->getEntityType()->getLabel();
    $blockItems[static::BLOCK_ITEM_KEY__OWNER] = $this->getOwnerField($node);
    $blockItems[static::BLOCK_ITEM_KEY__URLS] = $this->getUrlField($node, $block);
    $block['#markup'] = $this->renderBlockItems($blockItems);
    return $block;
  }

  /**
   * Calculate the 'Owner' field on the block.
   * 
   * This should be composed of links and look roughly like this:
   * 
   *   VAMC facilities » VISN 5 » VA Huntington health care
   * 
   * @param \Drupal\node\NodeInterface $node
   *   A valid node.
   * @return string
   *   A sensible value for the 'Owner' field.
   */
  public function getOwnerField(NodeInterface $node): string {
    $revision = $this->routeMatch->getParameter('node_revision');
    $result;
    if ($revision) {
      $result = $this->getSectionHierarchyBreadcrumbLinks($revision);
    }
    else {
      $result = $this->getSectionHierarchyBreadcrumbLinks($node);
    }
    return $result;
  }

  /**
   * Calculate the 'VA.gov URL' field on the block.
   * 
   * This should be a URL and considered either:
   * 
   * - "live", published (and therefore linked).
   * - "not live", not published yet (greyed out, not linked, '(pending)').
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node whose URL (or URLs!) will be displayed.
   * @param array &$block
   *   The block renderable, passed via reference for modification.
   *
   * @return string[] 
   *   An array of string URLs.
   */
  public function getUrlField(NodeInterface $node, array &$block): array {
    if (!$this->vaGovUrlShouldBeDisplayed($node)) {
      return [];
    }
    $urls = $this->getUrlsToDisplay($node);
    $isLive = $this->vaGovUrl->vaGovFrontEndUrlForEntityIsLive($node);
    $renderedUrls = $this->renderDisplayUrls($urls, $isLive);
    if (count($renderedUrls) && !$isLive) {
      $block['#attached']['library'][] = 'va_gov_workflow_assignments/ewa_style';

      // Cache the response for 5 minutes to avoid repeated longer
      // page loads if va.gov is not responding.
      $block['#cache']['max-age'] = 300;
    }
    return $renderedUrls;
  }

  /**
   * Returns the URLs to print (if any).
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node.
   *
   * @return string[]
   *   URL(s) to print.
   */
  public function getUrlsToDisplay(NodeInterface $node): array {
    $urls = [];
    $sectionId = $node->get('field_administration')->target_id;
    if (LovellOps::getLovellType($node) !== '') {
      $urls = LovellOps::buildArrayLovellUrls($sectionId, $this->vaGovUrl, $node);
    }
    else {
      $urls[] = $this->vaGovUrl->getVaGovFrontEndUrlForEntity($node);
    }
    return $urls;
  }

  /**
   * Render final HTML for the block.
   * 
   * @return string
   *   A string of HTML.
   */
  public function renderBlockItems(array $blockItems): string {
    $output = '';
    \Drupal::logger('nate')->error(json_encode($blockItems));
    foreach ($blockItems as $key => $item) {
      switch ($key) {
        case static::BLOCK_ITEM_KEY__OWNER:
          // Links are already html safe.
          // Translated via \Drupal\Core\TypedData\TranslatableInterface.
          $output .= '<div><span class="va-gov-entity-meta__title"><strong>' . $key . ': </strong></span><span class="va-gov-entity-meta__content">' . $item . '</span></div>';
          break;
        case static::BLOCK_ITEM_KEY__URLS:
          // We may have multiple URLs, e.g. Lovell Federal Health Care.
          foreach ($blockItems[$key] as $url) {
            $output .= $this->t('<div><span class="va-gov-entity-meta__title"><strong>@block_key: </strong></span><span class="va-gov-entity-meta__content">@va_gov_url</span></div>',
            [
              '@block_key' => $key,
              '@va_gov_url' => $url,
            ]);
          }
          break;
        default:
          $output .= $this->t('<div><span class="va-gov-entity-meta__title"><strong>@block_key: </strong></span><span class="va-gov-entity-meta__content">@block_item</span></div>',
          [
            '@block_key' => $key,
            '@block_item' => $item,
          ]);
          break;
      }
    }
    return $output;
  }

  /**
   * Render URLs for display.
   * 
   * @param array $urls
   *   A list of string URLs.
   * @param bool $isLive
   *   TRUE if the node is "live" or published.
   * 
   * @return string[]
   *   A list of rendered URLs, as strings.
   */
  public function renderDisplayUrls(array $urls, bool $isLive): array {
    return array_map(function ($url) use ($isLive) {
      return $this->renderUrl($url, $isLive);
    }, $urls);
  }

  /**
   * Renders a URL.
   * 
   * @param string $url
   *   The URL to render.
   * @param bool $isLive
   *   TRUE if the URL is "live" (published to the frontend).
   * 
   * @return string
   *   The rendered URL as HTML.
   */
  public function renderUrl(string $url, bool $isLive): string {
    return $isLive ? $this->renderLiveUrl($url) : $this->renderPendingUrl($url);
  }

  /**
   * Renders a "live" URL.
   * 
   * @param string $url
   *   The URL to render.
   * 
   * @return string
   *   The rendered URL as HTML.
   */
  public function renderLiveUrl(string $url): string {
    $link = Link::fromTextAndUrl($url, Url::fromUri($url))
      ->toRenderable();
    $link['#attributes'] = [
      'class' => 'va-gov-url',
    ];
    return $this->renderer->render($link);
  }

  /**
   * Renders a "pending" URL.
   * 
   * @param string $url
   *   The URL to render.
   * 
   * @return string
   *   The rendered URL as HTML.
   */
  public function renderPendingUrl(string $url): string {
    $markup = new FormattableMarkup('<span class="va-gov-url-pending">:url</span> (pending)', [
      ':url' => $url,
    ]);
    return (string) $markup;
  }

  /**
   * Returns a maximum of 3 section hierarchy breadcrumb links.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node with section information to display.
   * @return string
   *   Section breadcrumb links in hierarchy.
   */
  public function getSectionHierarchyBreadcrumbLinks(NodeInterface $node) : string {
    $links = $this->sectionHierarchyBreadcrumb->getHtmlLinks($node);
    // We exclude the first link and anything beyond the fourth.
    $links = array_slice($links, 1, 3);
    return implode(' » ', $links);
  }

  /**
   * Determine whether the va.gov URL should be displayed.
   *
   * @return bool
   *   Boolean value.
   */
  private function vaGovUrlShouldBeDisplayed(NodeInterface $node) : bool {
    // Make sure this isn't a staff page without bio.
    if (!empty($node->field_complete_biography_create) && $node->field_complete_biography_create->value === '0') {
      return FALSE;
    }

    if ($this->exclusionTypes->hasExcludedType($node)) {
      return FALSE;
    }

    $latestPublishedRevisionId = $this->editorialWorkflowContentRepository->getLatestPublishedRevisionId($node);
    if (!$latestPublishedRevisionId) {
      return FALSE;
    }

    $latestArchivedRevisionId = $this->editorialWorkflowContentRepository->getLatestArchivedRevisionId($node);
    if ($latestArchivedRevisionId > $latestPublishedRevisionId) {
      return FALSE;
    }

    return TRUE;
  }

}
