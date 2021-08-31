<?php

namespace Drupal\va_gov_workflow_assignments\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\va_gov_backend\Service\ExclusionTypesInterface;
use Drupal\va_gov_backend\Service\VaGovUrlInterface;
use Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepository;
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
 *     ),
 *     "node_revision" = @ContextDefinition(
 *       "entity:node_revision",
 *       label = @Translation("Node Revision"),
 *       required = FALSE,
 *     ),
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
   * @var \Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepository
   */
  protected $editorialWorkflowContentRepository;

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
    EditorialWorkflowContentRepository $editorialWorkflowContentRepository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->exclusionTypes = $exclusionTypes;
    $this->vaGovUrl = $vaGovUrl;
    $this->editorialWorkflowContentRepository = $editorialWorkflowContentRepository;
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
      $container->get('va_gov_workflow_assignments.editorial_workflow')
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
    $block_items['Content Type'] = $node->type->entity->label();

    $node_revision = $this->getNodeRevision();
    if ($node_revision) {
      $block_items['Owner'] = $this->getSectionHierarchyBreadcrumbLinks($node_revision);
    }
    else {
      $block_items['Owner'] = $this->getSectionHierarchyBreadcrumbLinks($node);
    }

    if ($this->vaGovUrlShouldBeDisplayed($node)) {
      $va_gov_url = $this->vaGovUrl->getVaGovFrontEndUrlForEntity($node);

      if ($this->vaGovUrl->vaGovFrontEndUrlForEntityIsLive($node)) {
        $link = Link::fromTextAndUrl($va_gov_url, Url::fromUri($va_gov_url))->toRenderable();
        $link['#attributes'] = ['class' => 'va-gov-url'];
        $block_items['VA.gov URL'] = render($link);
      }
      else {
        $block_items['VA.gov URL'] = new FormattableMarkup('<span class="va-gov-url-pending">' . $va_gov_url . '</span> (pending)', []);
        $block['#attached']['library'][] = 'va_gov_workflow_assignments/ewa_style';

        // Cache the response for 5 minutes to avoid repeated longer
        // page loads if va.gov is not responding.
        $block['#cache']['max-age'] = 300;
      }
    }

    $output = '';

    foreach ($block_items as $block_key => $block_item) {
      // Links are already html safe.
      // Translated via \Drupal\Core\TypedData\TranslatableInterface.
      if ($block_key === 'Owner') {
        $output .= '<div><span class="va-gov-entity-meta__title"><strong>' . $block_key . ': </strong></span><span class="va-gov-entity-meta__content">' . $block_item . '</span></div>';
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
    // Drupal sometimes hands us a nid and sometimes an upcasted node object.
    // @todo remove type checks when the patch at
    // https://www.drupal.org/project/drupal/issues/2730631
    // is committed. (Should be in 9.2)
    $route_parameter = $this->routeMatch->getParameter('node');
    if ($route_parameter instanceof NodeInterface) {
      return $route_parameter;
    }
    elseif (is_numeric($route_parameter)) {
      return $this->entityTypeManager
        ->getStorage('node')
        ->load($route_parameter);
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
    // Drupal sometimes hands us a nid and sometimes an upcasted node object.
    // @todo remove type checks when the patch at
    // https://www.drupal.org/project/drupal/issues/2730631
    // is committed. (Should be in 9.2)
    $route_parameter = $this->routeMatch->getParameter('node_revision');
    if ($route_parameter instanceof NodeInterface) {
      return $route_parameter;
    }
    elseif (is_numeric($route_parameter)) {
      return $this->entityTypeManager
        ->getStorage('node')
        ->loadRevision($route_parameter);
    }

    return NULL;
  }

  /**
   * Returns a maximum of 3 section hierarchy breadcrumb links.
   *
   * @return string
   *   Section breadcrumb links in hierarchy.
   */
  public function getSectionHierarchyBreadcrumbLinks(NodeInterface $node) : string {
    $links = [];
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $field_administration */
    $field_administration = $node->get('field_administration');
    $owner_term = $field_administration->referencedEntities() ?
      $field_administration->referencedEntities()[0] : [];

    if (!empty($owner_term)) {
      $links[] = $this->getTermLink($owner_term);
      $parents = array_values($this->entityTypeManager
        ->getStorage('taxonomy_term')
        ->loadAllParents($owner_term->id())
      );

      for ($i = 1; $i < 3; $i++) {
        if (!empty($parents[$i])) {
          array_unshift($links, $this->getTermLink($parents[$i]));
        }
      }
    }

    return implode(' » ', $links);
  }

  /**
   * Returns a section link.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   A taxonomy term.
   *
   * @return string
   *   A section link.
   */
  private function getTermLink(TermInterface $term) {
    return Link::fromTextAndUrl(
      $this->t(':name', [':name' => $term->get('name')->getString()]),
      $term->toUrl()
    )->toString();
  }

  /**
   * Determine whether the va.gov URL should be displayed.
   *
   * @return bool
   *   Boolean value.
   */
  private function vaGovUrlShouldBeDisplayed(NodeInterface $node) : bool {
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
