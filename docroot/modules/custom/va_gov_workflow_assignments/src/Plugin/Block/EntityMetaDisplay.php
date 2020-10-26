<?php

namespace Drupal\va_gov_workflow_assignments\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
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
    $block = [];
    $block_items = [];
    $sections = $this->getSections()['links'];
    $block_items['Owner'] = $sections;
    $node = $this->getNode();
    $node_revision = $this->getNodeRevision();
    $display_url = TRUE;

    if ($node) {
      $block_items['Content Type'] = $node->type->entity->label();

      // If this node type is excluded, do not attempt to show the URL.
      if (!empty($this->configFactory->getEditable('exclusion_types_admin.settings')->get('types_to_exclude'))) {
        $exclude_types = $this->configFactory->getEditable('exclusion_types_admin.settings')->get('types_to_exclude');
        if (in_array($node->bundle(), $exclude_types)) {
          $display_url = FALSE;
        }
      }

      // If there is an archived revision with an ID higher than the latest
      // published revision ID, do not attempt to show the URL.
      //
      // First, get the latest published revision ID.
      if ($display_url) {
        $query = \Drupal::database()->select('content_moderation_state_field_revision', 'cmr')
          ->condition('content_entity_id', $node->id(), '=')
          ->condition('moderation_state', 'published', '=')
          ->fields('cmr', ['content_entity_revision_id'])
          ->orderBy('content_entity_revision_id', 'DESC')
          ->range(0, 1);
        $result = $query->execute();
        $latest_published_revision_id = $result->fetchField();

        // Next, get the latest archived revision ID.
        $query = \Drupal::database()->select('content_moderation_state_field_revision', 'cmr')
          ->condition('content_entity_id', $node->id(), '=')
          ->condition('moderation_state', 'archived', '=')
          ->fields('cmr', ['content_entity_revision_id'])
          ->orderBy('content_entity_revision_id', 'DESC')
          ->range(0, 1);
        $result = $query->execute();
        $latest_archived_revision_id = $result->fetchField();

        // Do not show the URL if no revision has been published.
        if (!$latest_published_revision_id) {
          $display_url = FALSE;
        }

        // Do not show the URL if there is an archived revision that is newer
        // than the most recent published revision.
        if ($latest_published_revision_id && $latest_archived_revision_id && ($latest_archived_revision_id > $latest_published_revision_id)) {
          $display_url = FALSE;
        }
      }

      // The va.gov URL should be taken from the most recent published revision.
      if ($display_url) {
        // Generate the va.gov URL.
        $url = 'https://va.gov' . $node->toUrl()->toString();

        // See if the URL is live.
        $client = \Drupal::httpClient();
        $response = $client->head($url, ['http_errors' => FALSE]);

        switch ($response->getStatusCode()) {
          // If we get a 200, the URL is live - display it as normal.
          case 200:
            $link = Link::fromTextAndUrl($url, Url::fromUri($url))->toRenderable();
            $link['#attributes'] = ['class' => 'va-gov-url'];
            $block_items['VA.gov URL'] = render($link);
            break;

          // If we get a 404, the URL is not yet live - display it without
          // linking and add (pending) text.
          case 404:
            $block_items['VA.gov URL'] = new FormattableMarkup('<span class="va-gov-url-pending">' . $url . '</span> (pending)', []);
            $block['#attached']['library'][] = 'va_gov_workflow_assignments/ewa_style';
            break;

          // If we get some other response, cache the response for 5 minutes to
          // avoid long page loads if va.gov is not responding and do not
          // display anything.
          default:
            $block['#cache']['max-age'] = 300;
            break;
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
    $block['#markup'] = $output;

    return $block;
  }

  /**
   * Get the node from context if available.
   *
   * @return mixed
   *   Node if available, NULL if not.
   */
  public function getNode() {
    // Drupal sometimes hands us a nid and sometimes an upcasted node object.
    // @TODO remove type checks when the patch at
    // https://www.drupal.org/project/drupal/issues/2730631
    // is committed. (Should be in 9.2)
    if ($this->routeMatch->getParameter('node') instanceof NodeInterface) {
      return $this->routeMatch->getParameter('node');
    }
    elseif (is_numeric($this->routeMatch->getParameter('node'))) {
      return $this->entityTypeManager()
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
  public function getNodeRevision() {
    // Drupal sometimes hands us a nid and sometimes an upcasted node object.
    // @TODO remove type checks when the patch at
    // https://www.drupal.org/project/drupal/issues/2730631
    // is committed. (Should be in 9.2)
    if ($this->routeMatch->getParameter('node_revision') instanceof NodeInterface) {
      return $this->routeMatch->getParameter('node_revision');
    }
    elseif (is_numeric($this->routeMatch->getParameter('node_revision'))) {
      return $this->entityTypeManager()
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
