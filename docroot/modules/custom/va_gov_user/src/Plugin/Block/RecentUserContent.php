<?php

namespace Drupal\va_gov_user\Plugin\Block;

use Drupal\user\Entity\User;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\views\Views;

/**
 * Provides a block display of recent user activity.
 *
 * @Block(
 *   id = "va_gov_recent_user_content",
 *   admin_label = @Translation("Recent user content")
 * )
 */
class RecentUserContent extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The current path.
   *
   * @var Drupal\Core\Path\CurrentPathStack
   */
  protected $path;

  /**
   * The render object.
   *
   * @var Drupal\Core\Render\Renderer
   */
  protected $render;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $account, CurrentPathStack $path, Renderer $render) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
    $this->path = $path;
    $this->render = $render;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('path.current'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = '<p>' . $this->getUserHeader() . '</p>' . $this->getView();
    return [
      '#markup' => $output,
    ];
  }

  /**
   * Return the rendered view.
   *
   * @return object
   *   Recent user activity data.
   */
  public function getView() {
    $result = FALSE;
    $view = Views::getView('recent_content_activity');

    if ($view) {
      $view->setDisplay('block_user_recent_content');
      $view->execute();
      $rendered_view = $view->render();

      // Render the view.
      $result = $this->render->render($rendered_view);
    }

    return $result;
  }

  /**
   * Return the rendered view.
   *
   * @return object
   *   Recent user activity data.
   */
  public function getUserHeader() {
    $current_user = $this->account->id();
    $current_path = $this->path->getPath();
    $arg = explode('/', $current_path);
    $string = $this->t('You have worked on the following content.');
    if (!empty($arg[2]) && $arg[2] !== $current_user) {
      $user = User::load($arg[2]);
      $string = $this->t(':user has worked on the following content.',
      [
        ':user' => $user->getAccountName(),
      ]);
    }

    return $string;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

}
