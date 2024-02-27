<?php

namespace Drupal\va_gov_eca\Plugin\ECA\Condition;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\eca\EcaState;
use Drupal\eca\Plugin\ECA\Condition\ConditionBase;
use Drupal\eca\Token\TokenInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a Views result Condition plugin for ECA.
 *
 * @EcaCondition(
 *   id = "views_result",
 *   label = @Translation("Views Result"),
 *   description = @Translation("Views result condition.")
 * )
 */
class ViewsResultCondition extends ConditionBase {

  /**
   * The Drupal Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Constructs a new ViewsResultCondition.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type Manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The Entity Type Bundle Info service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The Request Stack service.
   * @param \Drupal\eca\Token\TokenInterface $token_services
   *   The ECA Token service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The Account Proxy service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The Time service.
   * @param \Drupal\eca\EcaState $state
   *   The ECS State service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Drupal Module Handler service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, RequestStack $request_stack, TokenInterface $token_services, AccountProxyInterface $current_user, TimeInterface $time, EcaState $state, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_type_bundle_info, $request_stack, $token_services, $current_user, $time, $state);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ConditionBase {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('request_stack'),
      $container->get('eca.token_services'),
      $container->get('current_user'),
      $container->get('datetime.time'),
      $container->get('eca.state'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function evaluate(): bool {
    $result = views_get_view_result($this->configuration['view_name'], $this->configuration['display_name'], $this->configuration['arguments']);
    return count($result) > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $view_storage = $this->entityTypeManager->getStorage('view');
    $displays = Views::getApplicableViews('eca_views_display');
    $options = [];
    foreach ($displays as $data) {
      [$view_id, $display_id] = $data;
      $view = $view_storage->load($view_id);
      $display = $view->get('display');
      $options[$view_id . ':' . $display_id] = $view_id . ' - ' . $display[$display_id]['display_title'];
    }

    if ($options) {
      $default = !empty($this->configuration['view_name']) && !empty($this->configuration['view_display']) ? $this->configuration['view_name'] . ':' . $this->configuration['view_display'] : '';
      $form['view']['view_and_display'] = [
        '#type' => 'select',
        '#title' => $this->t('View to query'),
        '#required' => TRUE,
        '#options' => $options,
        '#default_value' => $default,
        '#description' => '<p>' . $this->t('Choose the view and display to retrieve results from.<br />Only views with a display of type "ECA Result" are eligible.') . '</p>',
      ];

      $default = !empty($this->configuration['arguments']) ? implode(', ', $this->configuration['arguments']) : '';
      $form['view']['arguments'] = [
        '#type' => 'textfield',
        '#title' => $this->t('View arguments'),
        '#default_value' => $default,
        '#required' => FALSE,
        '#description' => $this->t('Provide a comma separated list of arguments to pass to the view.'),
      ];
    }
    else {
      if ($this->currentUser->hasPermission('administer views') && $this->moduleHandler->moduleExists('views_ui')) {
        $form['view']['no_view_help'] = [
          '#markup' => '<p>' . $this->t('No eligible views were found. <a href=":create">Create a view</a> with an <em>ECA Result</em> display, or add such a display to an <a href=":existing">existing view</a>.', [
            ':create' => Url::fromRoute('views_ui.add')->toString(),
            ':existing' => Url::fromRoute('entity.view.collection')->toString(),
          ]) . '</p>',
        ];
      }
      else {
        $form['view']['no_view_help']['#markup'] = '<p>' . $this->t('No eligible views were found.') . '</p>';
      }
    }
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $value = $form_state->getValue('view');
    // Split view name and display name from the 'view_and_display' value.
    if (!empty($value)) {
      [$view, $display] = explode(':', $value['view_and_display']);
    }
    else {
      $form_state->setError($form['view']['view_and_display'], new TranslatableMarkup('The views entity selection mode requires a view.'));
      return;
    }

    // Explode the 'arguments' string into an actual array.
    $arguments_string = trim($value['arguments']);
    if ($arguments_string === '') {
      $arguments = [];
    }
    else {
      $arguments = array_map('trim', explode(',', $arguments_string));
    }

    $value = [
      'view_name' => $view,
      'display_name' => $display,
      'arguments' => $arguments,
    ];
    $form_state->setValue('view', $value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $view = $form_state->getValue('view');
    $this->configuration['view_name'] = $view['view_name'];
    $this->configuration['display_name'] = $view['display_name'];
    $this->configuration['arguments'] = $view['arguments'];
    parent::submitConfigurationForm($form, $form_state);
  }

}
