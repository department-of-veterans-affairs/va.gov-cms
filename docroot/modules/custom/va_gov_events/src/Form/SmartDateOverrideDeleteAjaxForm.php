<?php

namespace Drupal\va_gov_events\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\smart_date_recur\Entity\SmartDateOverride;

/**
 * Provides AJAX handling of override deletion.
 */
class SmartDateOverrideDeleteAjaxForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "smart_date_recur_delete_override_ajaxform";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SmartDateOverride $entity = NULL) {
    $cancelurl = new Url('smart_date_recur.instances', [
      'rrule' => (int) $entity->rrule->value,
      'modal' => TRUE,
    ]);
    $submiturl = new Url('va_gov_events.instance.revert.ajax', [
      'entity' => $entity->id(),
      'confirm' => 1,
    ]);
    $form['#prefix'] = '<div id="manage-instances">';
    $form['#suffix'] = '</div>';
    $form['message'] = [
      '#markup' => $this->t('<strong>Reset this event?</strong><p>Date, time and location information will be restored to match the original event series.</p>'),
    ];
    $form['delete'] = [
      '#type' => 'link',
      '#title' => $this->t('Reset'),
      '#attributes' => [
        'class' => [
          'button',
          'button--primary',
          'use-ajax',
        ],
      ],
      '#url' => $submiturl,
      '#cache' => [
        'contexts' => [
          'url.query_args:destination',
        ],
      ],
    ];
    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => [
        'class' => [
          'button',
          'use-ajax',
        ],
      ],
      '#url' => $cancelurl,
      '#cache' => [
        'contexts' => [
          'url.query_args:destination',
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Implement submitForm() method.
  }

}
