<?php

namespace Drupal\va_gov_eca\Plugin\ECA\Condition;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\ECA\Condition\ConditionBase;

/**
 * Provides a Views result Condition plugin for ECA.
 *
 * @EcaCondition(
 *   id = "views_result",
 *   label = @Translation("Views Result"),
 *   description = @Translation("Views result condition.")
 * )
 */
class ViewsResult extends ConditionBase {

  /**
   * {@inheritDoc}
   */
  public function evaluate(): bool {
    $result = views_get_view_result($this->configuration['views_name'], $this->configuration['views_display']);
    return count($result) > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['views_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Views machine name'),
      '#default_value' => $this->configuration['views_name'],
    ];
    $form['views_display'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Views display id'),
      '#default_value' => $this->configuration['views_display'],
    ];
    $form['views_arguments'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Views arguments'),
      '#default_value' => $this->configuration['views_arguments'],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['views_name'] = $form_state->getValue('views_name');
    $this->configuration['views_display'] = $form_state->getValue('views_display');
    $this->configuration['views_arguments'] = $form_state->getValue('views_arguments');
    parent::submitConfigurationForm($form, $form_state);
  }

}
