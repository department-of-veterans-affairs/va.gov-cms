<?php

namespace Drupal\va_gov_user\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block of links to User Sections.
 *
 * @Block(
 *   id = "va_gov_user_sections",
 *   admin_label = @Translation("Sections")
 * )
 */
class UserSections extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Database
   */
  private $database;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, Connection $database, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get currently viewed profile.
    $user = $this->routeMatch->getParameter('user');

    // Other users need to see only sections assigned to their profiles.
    $query = $this->database->select('section_association__user_id', 'sau');
    $query->join('section_association', 'sa', 'sau.entity_id = sa.id');
    $query->condition('sau.user_id_target_id', $user->id());
    $query->fields('sa', ['section_id']);
    $results = $query->execute()->fetchCol();

    // Viewed profile is content_admin or administrator
    // OR
    // user has access to ALL sections -
    // Render sections tree.
    if ($user->hasRole('content_admin') || $user->hasRole('administrator') || in_array('administration', $results)) {
      $view = Views::getView('sections_tree');
      $view->setDisplay('block_1');
      $view->preExecute();
      $view->execute();

      return $view->buildRenderable();
    }

    $entity_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $terms = $entity_storage->loadMultiple($results);

    $links = [];

    foreach ($terms as $term) {
      $links[$term->id()] = [
        'title' => $term->getName(),
        'url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]),
      ];
    }

    if (!empty($links)) {
      return [
        'links' => [
          '#theme' => 'links',
          '#links' => $links,
        ],
      ];
    }
    else {
      // User is not assigned to any sections, display onboarding lingo.
      // @todo: Update lingo.
      return [
        'section_assignment' => [
          '#markup' => $this->t('Contact #cms-support for VA section assignment.'),
        ],
      ];
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Only display on User View route.
    return AccessResult::allowedif($this->routeMatch->getRouteName() === 'entity.user.canonical');
  }

}
