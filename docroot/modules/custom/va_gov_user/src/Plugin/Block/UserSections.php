<?php

namespace Drupal\va_gov_user\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\va_gov_user\Service\UserPermsService;
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
   * The user section permissions service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPerms;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, UserPermsService $user_perms) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->userPerms = $user_perms;
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
      $container->get('va_gov_user.user_perms')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['url.path', 'user'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['user:' . $this->routeMatch->getParameter('user')->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get currently viewed profile.
    $user = $this->routeMatch->getParameter('user');

    // Get sections assigned to user profile.
    $sections = $this->userPerms->getSections($user);

    // Viewed profile is content_admin or administrator
    // OR
    // user has access to ALL sections -
    // Render sections tree.
    if ($user->hasRole('content_admin') || $user->hasRole('administrator') || in_array('All sections', $sections)) {
      $view = Views::getView('sections_tree');
      $view->setDisplay('block_1');
      $view->preExecute();
      $view->execute();

      return $view->buildRenderable();
    }

    // Use has access only to some sections.
    // Compose list.
    $links = [];

    foreach ($sections as $tid => $term_name) {
      $links[$tid] = [
        'title' => $term_name,
        'url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $tid]),
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
