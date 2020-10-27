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
use Drupal\taxonomy\Entity\Term;
use Drupal\va_gov_backend\Service\ExclusionTypesInterface;
use Drupal\va_gov_backend\Service\VaGovUrl;
use Drupal\va_gov_workflow_assignments\Service\EditorialWorkflow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block of Meta information for entities.
 *
 * @Block(
 *   id = "va_gov_workflow_assignments_meta",
 *   admin_label = @Translation("Entity Meta Display"),
 *   context = {
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
   * @var \Drupal\va_gov_backend\ExclusionTypesInterface
   */
  protected $exclusionTypes;

  /**
   * The va.gov URL service.
   *
   * @var \Drupal\va_gov_backend\Service\VaGovUrl
   */
  protected $vaGovUrl;

  /**
   * The Editorial Workflow service.
   *
   * @var \Drupal\va_gov_workflow_assignments\Service\EditorialWorkflow
   */
  protected $editorialWorkflow;

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
    VaGovUrl $vaGovUrl,
    EditorialWorkflow $editorialWorkflow
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->exclusionTypes = $exclusionTypes;
    $this->vaGovUrl = $vaGovUrl;
    $this->editorialWorkflow = $editorialWorkflow;
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
      $container->get('database'),
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

    $node_revision = $this->getNodeRevision();
    $block = [];
    $block_items = [];

    $block_items['Owner'] = $this->getSections($node, $node_revision)['links'];
    $block_items['Content Type'] = $node->type->entity->label();

    if ($this->vaGovUrlShouldBeDisplayed($node)) {
      $va_gov_url = $this->vaGovUrl->getVaGovUrlForEntity($node);

      if ($this->vaGovUrl->getVaGovUrlStatusForEntity($node) === 200) {
        $link = Link::fromTextAndUrl($va_gov_url, Url::fromUri($va_gov_url))->toRenderable();
        $link['#attributes'] = ['class' => 'va-gov-url'];
        $block_items['VA.gov URL'] = render($link);
      }
      else {
        $block_items['VA.gov URL'] = new FormattableMarkup('<span class="va-gov-url-pending">' . $va_gov_url . '</span> (pending)', []);
        $block['#attached']['library'][] = 'va_gov_workflow_assignments/ewa_style';

        // Cache the response for 5 minutes to avoid long page loads if va.gov
        // is not responding.
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
    // @TODO remove type checks when the patch at
    // https://www.drupal.org/project/drupal/issues/2730631
    // is committed. (Should be in 9.2)
    if ($this->routeMatch->getParameter('node') instanceof NodeInterface) {
      return $this->routeMatch->getParameter('node');
    }
    elseif (is_numeric($this->routeMatch->getParameter('node'))) {
      return $this->entityTypeManager
        ->getStorage('node')
        ->load($this->routeMatch->getParameter('node'));
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
    // @TODO remove type checks when the patch at
    // https://www.drupal.org/project/drupal/issues/2730631
    // is committed. (Should be in 9.2)
    if ($this->routeMatch->getParameter('node_revision') instanceof NodeInterface) {
      return $this->routeMatch->getParameter('node_revision');
    }
    elseif (is_numeric($this->routeMatch->getParameter('node_revision'))) {
      return $this->entityTypeManager
        ->getStorage('node')
        ->loadRevision($this->routeMatch->getParameter('node_revision'));
    }

    return NULL;
  }

  /**
   * Returns max of 3 section hierarchy of breadcrumbs and section term ids.
   *
   * @return array
   *   All section breadcrumbs in hierarchy and term ids.
   */
  public function getSections($node, $node_revision) {
    $sections = [];
    $tids = [];

    // Grab our current section.
    $tid = $node_revision ? $node_revision->get('field_administration')->getString() : $node->get('field_administration')->getString();
    if (!empty($tid)) {
      $tids[] = $tid;
      $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
      $loaded_tid = $term_storage->load($tid);

      // Feed it to our link builder.
      $sections[] = $this->getLink($loaded_tid);

      $tid_parent_raw = $term_storage->loadParents($tid);
      // If we have a parent, process.
      if (!empty($tid_parent_raw)) {
        $tid_parent = reset($tid_parent_raw);
        $tid_parent_id = $tid_parent->id();
        $tids[] = $tid_parent_id;

        // Feed it to our link builder.
        $sections[] = $this->getLink($tid_parent);

        $tid_grandparent_raw = $term_storage->loadParents($tid_parent_id);
        // If we have a grandparent, process.
        if (!empty($tid_grandparent_raw)) {
          $tid_grandparent = reset($tid_grandparent_raw);
          $tid_grandparent_id = $tid_grandparent->id();
          $tids[] = $tid_grandparent_id;

          // Feed it to our link builder.
          $sections[] = $this->getLink($tid_grandparent, TRUE);
        }
      }
    }

    return [
      'links' => implode(' ', array_reverse($sections)),
      'tids' => $tids,
    ];
  }

  /**
   * Returns a section breadcrumb.
   *
   * @param \Drupal\taxonomy\Entity\Term $term
   *   A loaded taxonomy term.
   * @param bool $first
   *   Bool to determine if first item in Section breadcrumb.
   *
   * @return string
   *   A Section breadcrumb.
   */
  private function getLink(Term $term, bool $first = FALSE) {
    $term_url = Url::fromRoute('entity.taxonomy_term.canonical', [
      'taxonomy_term' => $term->id(),
    ]);
    $caret = $first == TRUE ? '' : ' » ';

    return Link::fromTextAndUrl($this->t(':caret:name', [
      ':caret' => $caret,
      ':name' => $term->get('name')->getString(),
    ]), $term_url)->toString();
  }

  /**
   * Determine whether the va.gov URL should be displayed.
   *
   * @return bool
   *   Boolean value.
   */
  private function vaGovUrlShouldBeDisplayed($node) {
    if ($this->exclusionTypes->typeIsExcluded($node->bundle())) {
      return FALSE;
    }

    $latest_published_revision_id = $this->editorialWorkflow->getLatestPublishedRevisionId($node);
    if (!$latest_published_revision_id) {
      return FALSE;
    }

    // Do not show the URL if there is an archived revision that is newer
    // than the most recent published revision.
    $latest_archived_revision_id = $this->editorialWorkflow->getLatestArchivedRevisionId($node);
    if ($latest_published_revision_id && $latest_archived_revision_id &&
         ($latest_archived_revision_id > $latest_published_revision_id)) {
      return FALSE;
    }

    return TRUE;
  }

}
