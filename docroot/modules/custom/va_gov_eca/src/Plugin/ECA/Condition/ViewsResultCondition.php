<?php

namespace Drupal\va_gov_eca\Plugin\ECA\Condition;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\eca\Plugin\ECA\Condition\ConditionBase;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->module_handler = $container->get('module_handler');
    return $instance;
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
