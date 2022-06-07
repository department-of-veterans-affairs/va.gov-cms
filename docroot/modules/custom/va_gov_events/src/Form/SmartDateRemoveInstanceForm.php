<?php

namespace Drupal\va_gov_events\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\smart_date_recur\Entity\SmartDateOverride;
use Drupal\smart_date_recur\Entity\SmartDateRule;
use Drupal\va_gov_events\Controller\Instances;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an instance cancellation confirmation form for Smart Date.
 */
class SmartDateRemoveInstanceForm extends ConfirmFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * ID of the rrule being used.
   *
   * @var \Drupal\smart_date_recur\Entity\SmartDateRule
   */
  protected $rrule;

  /**
   * Index of the instance to delete.
   *
   * @var int
   */
  protected $index;

  /**
   * ID of an existing override.
   *
   * @var int
   */
  protected $oid;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FormBuilder $form_builder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "smart_date_recur_remove_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SmartDateRule $rrule = NULL, string $index = NULL, $ajax = FALSE) {
    $this->rrule = $rrule;
    $this->index = $index;
    $result = $this->entityTypeManager->getStorage('smart_date_override')
      ->getQuery()
      ->condition('rrule', $rrule->id())
      ->condition('rrule_index', $index)
      ->execute();
    if ($result && $override = SmartDateOverride::load(array_pop($result))) {
      $this->oid = $override->id();
    }
    $form = parent::buildForm($form, $form_state);
    if ($ajax) {
      $this->addAjaxWrapper($form);
      $form['actions']['cancel']['#title'] = $this->t('No, keep event instance');
      $form['actions']['cancel']['#attributes']['class'][] = 'use-ajax';
      $form['actions']['cancel']['#url']->setRouteParameter('modal', TRUE);
      $form['actions']['submit']['#ajax'] = ['callback' => '::ajaxSubmit'];
    }
    return $form;
  }

  /**
   * Ajax submit function.
   *
   * @param array $form
   *   The form values being submitted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state being submitted.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response from the AJAX form submit.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->disableRedirect();
    $instanceController = new Instances($this->entityTypeManager, $this->formBuilder);
    $instanceController->setSmartDateRule($this->rrule);
    $instanceController->setUseAjax(TRUE);
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#manage-instances', $instanceController->listInstancesOutput()));
    return $response;
  }

  /**
   * Adding a wrapper to the form, for ajax targeting.
   *
   * @param array $form
   *   The form array to be enclosed.
   */
  protected function addAjaxWrapper(array &$form) {
    $form['#prefix'] = '<div id="manage-instances">';
    $form['#suffix'] = '</div>';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $question = $this->t('<p>You can undo this later by resetting to the original settings for the event series.</p>');
    if ($this->oid) {
      $question .= ' ' . $this->t('Your existing overridden data will be deleted.');
    }
    return $question;
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $rrule = $this->rrule->id();
    return new Url('smart_date_recur.instances', ['rrule' => $rrule]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Yes, cancel event instance');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('<strong>Cancel this event instance?</strong>');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $instanceController = new Instances($this->entityTypeManager, $this->formBuilder);
    $instanceController->setSmartDateRule($this->rrule);
    $instanceController->removeInstance($this->index, $this->oid);

    if (!isset($form['actions']['cancel'])) {
      $instanceController = new Instances($this->entityTypeManager, $this->formBuilder);
      // Force refresh of parent entity.
      $instanceController->applyChanges($this->rrule);
      // Output message about operation performed.
      $this->messenger()->addMessage($this->t('The instance has been removed.'));
    }
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
