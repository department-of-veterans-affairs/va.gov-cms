<?php

namespace Drupal\va_gov_eca\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\message\Entity\Message;
use Drupal\node\Entity\Node;

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
    $templates = $this->entityTypeManager->getStorage('message_template')->getQuery()->execute();
    $form['message_template'] = [
      '#type' => 'select',
      '#title' => $this->t('Message Template'),
      '#description' => $this->t('Select the Message Template to use for this action. The message template body will become the body of the email message.'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['message_template'] ?? '',
      '#options' => $templates,
    ];
    $form['message_template_variables'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message Template Variables'),
      '#description' => $this->t('Data to map to the template using template variables. One mapping per line, entered as [message-token-name]:[value]'),
      '#required' => FALSE,
      '#default_value' => $this->configuration['message_template_variables'] ?? '',
    ];
    $form['recipient'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Recipient'),
      '#description' => $this->t('The recipient (to:) of the email message. Tokens allowed.'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['recipient'] ?? '',
    ];
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#description' => $this->t('The email subject. Tokens allowed.'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['subject'] ?? '',
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
    $node = Node::load(65970);
    // TODO: Change to use a queue. We are sending directly for now for testing purposes.
    $message = Message::create([
      'template' => $this->configuration['message_template'],
      'uid' => $node->getOwnerId(),
    ]);
    $message->set('field_expiration_date', '1/3/2024');
    $message->set('field_target_entity', $node->id());
    $message->save();

    /** @var \Drupal\message_notify\MessageNotifier $notifier */
    $notifier = \Drupal::service('message_notify.sender');
    $notifier->send($message);
  }

}
