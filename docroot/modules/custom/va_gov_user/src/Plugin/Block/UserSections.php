<?php

namespace Drupal\va_gov_user\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\va_gov_user\Service\UserPermsService;
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, UserPermsService $user_perms) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
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

    // User has access only to some sections.
    // Compose list.
    $entity_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $tree = $entity_storage->loadTree('administration');

    foreach ($tree as $key => $term) {
      if (!array_key_exists($term->tid, $sections)) {
        unset($tree[$key]);
      }
    }

    $links = [];

    foreach ($tree as $term) {
      $parent_path = $term->parents[0] ? $this->getArrayKeyPath($links, $term->parents[0]) : NULL;

      // Compose render array of section links while preserving hierarchy.
      // This switch accounts for taxonomy terms that are 5 levels deep.
      // Sections vocabulary is 4 levels deep. If it grows over 5, this logic
      // must be expanded.
      $count = count($parent_path);
      switch ($count) {
        case 1:
          $links[$parent_path[0]]['items']['#attributes'] = [
            'id' => 'section-' . $parent_path[0],
            'class' => 'subsections',
          ];
          $links[$parent_path[0]]['items'][$term->tid] = [
            '#type' => 'link',
            '#weight' => $term->weight,
            '#title' => $term->name,
            '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid]),
          ];
          break;

        case 3:
          $links[$parent_path[2]][$parent_path[1]][$parent_path[0]]['items'][$term->tid] = [
            '#type' => 'link',
            '#weight' => $term->weight,
            '#title' => $term->name,
            '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid]),
          ];
          break;

        case 5:
          $links[$parent_path[4]][$parent_path[3]][$parent_path[2]][$parent_path[1]][$parent_path[0]]['items'][$term->tid] = [
            '#type' => 'link',
            '#weight' => $term->weight,
            '#title' => $term->name,
            '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid]),
          ];
          break;

        default:
          $section_link = Link::fromTextAndUrl($term->name, Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid]))->toString();
          $expand_button = $sections[$term->tid]['hasChildren'] ? Markup::create('<button class="toggle" aria-label="Toggle ' . $term->name . ' section" aria-pressed="false" aria-expanded="false" aria-controls="section-' . $term->tid . '"></button>') : NULL;
          $links[$term->tid] = [
            '#type' => 'markup',
            '#markup' => $section_link . $expand_button,
            '#allowed_tags' => [
              'a',
              'button',
            ],
          ];
          break;
      }
    }

    if (!empty($links)) {
      return [
        'links' => [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
          '#items' => $links,
          '#attributes' => ['class' => 'sections'],
          '#attached' => [
            'library' => [
              'va_gov_user/sections_accordion',
            ],
          ],
          '#prefix' => $this->t('You can edit content in the following VA.gov sections.'),
        ],
      ];
    }
    else {
      // User is not assigned to any sections, display onboarding lingo.
      $url = 'mailto:vacmssupport@va.gov?subject=Section%20assignment&body=Dear%20VACMS%20support%20team%2C%0A%5BThis%20is%20a%20template.%20%20You%20can%20delete%20the%20text%20you%20don%E2%80%99t%20need%2C%20and%20feel%20free%20to%20add%20your%20own.%5D%0A%0AI%E2%80%99m%20a%20new%20CMS%20user%2C%20and%20need%20to%20be%20given%20access%20to%20the%20following%20VA.gov%20sections%3A%0A%5BList%20the%20sections%20you%20need%20access%20to%20here.%20If%20you%20aren%E2%80%99t%20sure%2C%20describe%20your%20job%20title%20and%20what%20pages%20you%20need%20to%20work%20on.%5D%0A%0APlease%20assign%20me%20the%20following%20role%3A%20%0A%5BAdd%20which%20role%20you%20need%20here.%5D%0A-%20Content%20editor%3A%20because%20I%20need%20to%20create%2C%20edit%2C%20and%20review%20content%0A-%20Content%20publisher%3A%20because%20I%20also%20need%20to%20publish%20content%0A-%20Content%20admin%3A%20because%20I%20need%20broad%20permissions%2C%20including%20customizing%20URLs%20and%20triggering%20unscheduled%20content%20releases%0A%0AThank%20you.';
      return [
        'section_assignment' => [
          '#markup' => $this->t('You don\'t have permission to access content in any VA.gov sections yet. <a href=":link">Contact VACMS Support to request access</a>.', [':link' => $url]),
        ],
      ];
    }
  }

  /**
   * Return a path for a specified array key.
   *
   * @param array $array
   *   Array.
   * @param string $lookup
   *   Array key to look up.
   *
   * @return array|null
   *   Array of elements that compose a path to searched key.
   */
  protected function getArrayKeyPath(array $array, $lookup) {
    if (array_key_exists($lookup, $array)) {
      return [$lookup];
    }
    else {
      foreach ($array as $key => $subarray) {
        if (is_array($subarray) && (is_int($key) || $key === 'items')) {
          $path = $this->getArrayKeyPath($subarray, $lookup);
          if ($path) {
            $path[] = $key;
            return $path;
          }
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Only display on User View route.
    return AccessResult::allowedif($this->routeMatch->getRouteName() === 'entity.user.canonical');
  }

}
