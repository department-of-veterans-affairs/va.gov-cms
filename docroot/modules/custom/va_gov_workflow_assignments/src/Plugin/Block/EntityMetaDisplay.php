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
use Drupal\va_gov_backend\Service\ExclusionTypesInterface;
use Drupal\va_gov_backend\Service\VaGovUrlInterface;
use Drupal\va_gov_lovell\LovellOps;
use Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepositoryInterface;
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

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @var \Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepositoryInterface
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
    EntityTypeManagerInterface $entity_type_manager,
    ExclusionTypesInterface $exclusionTypes,
    VaGovUrlInterface $vaGovUrl,
    EditorialWorkflowContentRepositoryInterface $editorialWorkflowContentRepository,
    RendererInterface $renderer,
    SectionHierarchyBreadcrumbInterface $sectionHierarchyBreadcrumb
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
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
    $node = $this->getNode();
    if (!$node) {
      return;
    }

    $block = $block_items = [];
    $block_items['Content Type'] = $this->entityTypeManager
      ->getStorage('node_type')
      ->load($node->bundle())
      ->label();

    $block_items['Owner'] = $this->getSectionHierarchyBreadcrumbLinks($this->getNodeRevision() ?? $node);
    if ($this->vaGovUrlShouldBeDisplayed($node)) {
      $va_gov_urls_to_display = $this->getUrlsToDisplay($node);
      foreach ($va_gov_urls_to_display as $va_gov_url) {
        if ($this->vaGovUrl->vaGovFrontEndUrlIsLive($va_gov_url)) {
          $link = Link::fromTextAndUrl($va_gov_url, Url::fromUri($va_gov_url))->toRenderable();
          $link['#attributes'] = ['class' => 'va-gov-url'];
          $block_items['VA.gov URL'][] = $this->renderer->render($link);
        }
        else {
          $block_items['VA.gov URL'][] = new FormattableMarkup('<span class="va-gov-url-pending">' . $va_gov_url . '</span> (pending)', []);
          $block['#attached']['library'][] = 'va_gov_workflow_assignments/ewa_style';

          // Cache the response for 5 minutes to avoid repeated longer
          // page loads if va.gov is not responding.
          $block['#cache']['max-age'] = 300;
        }
      }
    }

    $output = '';

    foreach ($block_items as $block_key => $block_item) {
      // Links are already html safe.
      // Translated via \Drupal\Core\TypedData\TranslatableInterface.
      if ($block_key === 'Owner') {
        $output .= '<div><span class="va-gov-entity-meta__title"><strong>' . $block_key . ': </strong></span><span class="va-gov-entity-meta__content">' . $block_item . '</span></div>';
      }
      elseif ($block_key === 'VA.gov URL') {
        // We may have multiple URLs, e.g. Lovell Federal Health Care.
        foreach ($block_items[$block_key] as $va_gov_url) {
          $output .= $this->t('<div><span class="va-gov-entity-meta__title"><strong>@block_key: </strong></span><span class="va-gov-entity-meta__content">@va_gov_url</span></div>',
          [
            '@block_key' => $block_key,
            '@va_gov_url' => $va_gov_url,
          ]);
        }
      }
      else {
        $output .= $this->t('<div><span class="va-gov-entity-meta__title"><strong>@block_key: </strong></span><span class="va-gov-entity-meta__content">@block_item</span></div>',
        [
          '@block_key' => $block_key,
          '@block_item' => $block_item,
        ]);
      }
    }
    $block['#markup'] = $output;

    return $block;
  }

  /**
   * Get the node from context if available.
   *
   * @return mixed
   *   Node if available, NULL if not.
   */
  private function getNode() {
    $route_parameter = $this->routeMatch->getParameter('node');
    if ($route_parameter instanceof NodeInterface) {
      return $route_parameter;
    }
    return NULL;
  }

  /**
   * Get the node revision in context if available.
   *
   * @return mixed
   *   Node if available, NULL if not.
   */
  private function getNodeRevision() {
    $route_parameter = $this->routeMatch->getParameter('node_revision');
    if ($route_parameter instanceof NodeInterface) {
      return $route_parameter;
    }
    return NULL;
  }

  /**
   * Returns a maximum of 3 section hierarchy breadcrumb links.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node with section information to display.
   *
   * @return string
   *   Section breadcrumb links in hierarchy.
   */
  public function getSectionHierarchyBreadcrumbLinks(NodeInterface $node) : string {
    $links = $this->sectionHierarchyBreadcrumb->getLinksHtml($node);
    // We include only up to the final 3 links.
    $links = array_slice($links, -3, 3);
    return implode(' » ', $links);
  }

  /**
   * Returns the URLs to print (if any).
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node.
   *
   * @return array
   *   URL(s) to print.
   */
  private function getUrlsToDisplay(NodeInterface $node): array {
    $va_gov_urls_to_display = [];
    $section_id = $node->get('field_administration')->target_id;
    if (LovellOps::getLovellType($node) !== '') {
      $va_gov_urls_to_display = LovellOps::buildArrayLovellUrls($section_id, $this->vaGovUrl, $node);
    }
    else {
      $va_gov_url = $this->vaGovUrl->getVaGovFrontEndUrlForEntity($node);
      $va_gov_urls_to_display[] = $va_gov_url;
    }

    return $va_gov_urls_to_display;
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

    if ($this->exclusionTypes->typeIsExcluded($node->bundle())) {
      return FALSE;
    }

    $latest_published_revision_id = $this->editorialWorkflowContentRepository->getLatestPublishedRevisionId($node);
    if (!$latest_published_revision_id) {
      return FALSE;
    }

    $latest_archived_revision_id = $this->editorialWorkflowContentRepository->getLatestArchivedRevisionId($node);
    if ($latest_archived_revision_id > $latest_published_revision_id) {
      return FALSE;
    }

    return TRUE;
  }

}
