<?php

namespace Drupal\va_gov_workflow_assignments\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block of Meta information for entities.
 *
 * @Block(
 *   id = "va_gov_workflow_assignments_meta",
 *   admin_label = @Translation("Entity Meta Display"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
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
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, ConfigFactory $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $configFactory;
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
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block_items = [];
    $sections = $this->getSections()['links'];
    $block_items['Owner'] = $sections;
    $nid = '';
    if ($this->routeMatch->getParameter('node') instanceof NodeInterface) {
      $node = $this->routeMatch->getParameter('node');
      $block_items['Content Type'] = $node->type->entity->label();

      // Make sure this bundle is okay to display on va.gov.
      $exclude_types = $this->configFactory->getEditable('exclusion_types_admin.settings')->get('types_to_exclude');
      // If exclude types in not empty then continue.
      if (!empty($exclude_types)) {
        // If active revision isn't published, it doesn't get included in build.
        // Sufficient to check moderation state on node = active revision.
        if (!in_array($node->bundle(), $exclude_types) && $node->get('moderation_state')->getString() === 'published') {
          $nid = $node->id();
          $url = 'https://va.gov' . Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString();
          $block_items['VA.gov URL'] = Link::fromTextAndUrl($url, Url::fromUri($url))->toString();
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
      else {
        $output .= $this->t('<div><span class="va-gov-entity-meta__title"><strong>@block_key: </strong></span><span class="va-gov-entity-meta__content">@block_item</span></div>',
        [
          '@block_key' => $block_key,
          '@block_item' => $block_item,
        ]);
      }
    }
    return [
      '#markup' => $output,
    ];

  }

  /**
   * Returns max of 3 section hierarchy of breadcrumbs and section term ids.
   *
   * @return array
   *   All section breadcrumbs in hierarchy and term ids.
   */
  public function getSections() {
    if ($this->getContextValue('node') instanceof NodeInterface) {
      $sections = [];
      $tids = [];
      $node = $this->getContextValue('node');

      // Grab our current section.
      $tid = $node->get('field_administration')->getString();
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

}
