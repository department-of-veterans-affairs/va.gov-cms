<?php

namespace Drupal\va_gov_eca\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;

/**
 * Action to queue an Email Message.
 *
 * @Action(
 *   id = "va_gov_eca_queue_email_message",
 *   label = @Translation("Queue an email Message"),
 *   description = @Translation("Queue an email using a Message template.")
 * )
 */
class QueueMessage extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['queue'] = [
      '#type' => 'textfield',
      '#title' => $this->t('queue'),
      '#description' => $this->t('The queue worker name to use for processing. Defaults to %worker', ['%worker' => 'va_gov_eca_message_worker']),
      '#required' => TRUE,
      '#default_value' => $this->configuration['queue'] ?? 'va_gov_eca_message_worker',
    ];
    $form['message_template'] = [
      '#type' => 'textfield',
      '#title' => $this->t(''),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => $this->configuration[''] ?? '',
    ];
    $form['message_template_variables'] = [
      '#type' => 'textfield',
      '#title' => $this->t(''),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => $this->configuration[''] ?? '',
    ];
    $form['recipient'] = [
      '#type' => 'textfield',
      '#title' => $this->t(''),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => $this->configuration[''] ?? '',
    ];
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t(''),
      '#description' => $this->t(''),
      '#required' => TRUE,
      '#default_value' => $this->configuration[''] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['queue'] = $form_state->getValue('queue');
    $this->configuration['message_template'] = $form_state->getValue('message_template');
    $this->configuration['message_template_variables'] = $form_state->getValue('message_template_variables');
    $this->configuration['recipient'] = $form_state->getValue('recipient');
    $this->configuration['subject'] = $form_state->getValue('subject');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function execute() {
    // TODO: Implement execute() method.
  }

}
