<?php

namespace Drupal\va_gov_notifications\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_notifications\Entity\NoActiveUsersRecipient;

/**
 * Form controller for ad hoc recipient add/edit forms.
 */
class NoActiveUsersRecipientForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\va_gov_notifications\Entity\NoActiveUsersRecipient $recipient */
    $recipient = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
      '#default_value' => $recipient->label(),
      '#maxlength' => 255,
      '#description' => $this->t('Human-readable label for this recipient.'),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $recipient->id(),
      '#machine_name' => [
        'exists' => '\\Drupal\\va_gov_notifications\\Entity\\NoActiveUsersRecipient::load',
      ],
      '#disabled' => !$recipient->isNew(),
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#default_value' => $recipient->getEmail(),
      '#description' => $this->t('Email address to include in monthly missing-editor notifications.'),
    ];

    $form['notes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Notes'),
      '#required' => FALSE,
      '#default_value' => $recipient->getNotes(),
      '#description' => $this->t('Optional context for why this recipient is included.'),
    ];

    $form['products'] = [
      '#type' => 'select',
      '#title' => $this->t('Product'),
      '#description' => $this->t('Limit this recipient to selected products. Leave empty to receive notifications for all products.'),
      '#multiple' => TRUE,
      '#options' => NoActiveUsersRecipient::PRODUCT_OPTIONS,
      '#default_value' => $recipient->getProducts(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $recipient->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $recipient = $this->entity;
    $status = $recipient->save();

    $this->messenger()->addStatus(
      $status === SAVED_UPDATED
      ? $this->t('Recipient %label has been updated.', ['%label' => $recipient->label()])
      : $this->t('Recipient %label has been created.', ['%label' => $recipient->label()])
    );

    $form_state->setRedirectUrl($recipient->toUrl('collection'));
    return $status;
  }

}
