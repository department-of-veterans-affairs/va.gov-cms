<?php

namespace Drupal\va_gov_events\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\smart_date\Plugin\Field\FieldWidget\SmartDateWidgetBase;
use Drupal\smart_date_recur\Entity\SmartDateOverride;
use Drupal\va_gov_events\Controller\Instances;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Smart Date Recur instace override edit forms.
 *
 * @ingroup smart_date_recur
 */
class SmartDateOverrideForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Renderer $renderer, FormBuilder $form_builder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smart_date_override_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $rrule = NULL, $index = NULL, $ajax = FALSE) {

    // @todo Show some kind of entity summary? Title at least?
    $instances = $rrule->getRuleInstances();
    if ($ajax) {
      $form['#prefix'] = '<div id="manage-instances">';
      $form['#suffix'] = '</div>';
    }
    // Get field config.
    $field_config = FieldConfig::loadByName(
      $rrule->get('entity_type')->getString(),
      $rrule->get('bundle')->getString(),
      $rrule->get('field_name')->getString()
    );
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_config */
    $defaults = $field_config->getDefaultValueLiteral()[0];

    $values['start'] = DrupalDateTime::createFromTimestamp($instances[$index]['value']);
    $values['end'] = DrupalDateTime::createFromTimestamp($instances[$index]['end_value']);
    $values['duration'] = ($instances[$index]['end_value'] - $instances[$index]['value']) / 60;

    $element = [
      'value' => [
        '#type' => 'datetime',
      ],
    ];
    SmartDateWidgetBase::createWidget($element, $values, $defaults);
    // Add the wrapper class for the Smart Date field so JS and CSS apply.
    $element['#attributes']['class'][] = 'smartdate--widget';
    $form['override'] = $element;
    $form['#attached']['library'][] = 'smart_date/smart_date';

    // Pass in values to identify the override.
    $form['rrule'] = [
      '#type' => 'hidden',
      '#value' => $rrule->id(),
    ];
    $form['rrule_index'] = [
      '#type' => 'hidden',
      '#value' => $index,
    ];
    if (!empty($instances[$index]['oid'])) {
      $form['oid'] = [
        '#type' => 'hidden',
        '#value' => $instances[$index]['oid'],
      ];
    }
    if ($ajax) {
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#button_type' => 'primary',
        '#ajax' => [
          'callback' => '::ajaxSubmit',
        ],
      ];
      $cancelurl = new Url('smart_date_recur.instances', [
        'rrule' => (int) $rrule->id(),
        'modal' => TRUE,
      ]);
      $form['ajaxcancel'] = [
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
    }
    else {
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#button_type' => 'primary',
      ];
    }
    return $form;
  }

  /**
   * Ajax submit function.
   *
   * @param array $form
   *   The render array of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state to submit.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The return value of the AJAX submission.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $status_messages = ['#type' => 'status_messages'];
    $messages = $this->renderer->renderRoot($status_messages);
    if (!empty($messages)) {
      $response->addCommand(new RemoveCommand('.messages-list'));
      $response->addCommand(new PrependCommand('#manage-instances', $messages));
      return $response;
    }
    $form_state->disableRedirect();
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $this->entityTypeManager;
    /** @var \Drupal\smart_date_recur\Entity\SmartDateRule $rrule */
    $rrule = $entityTypeManager->getStorage('smart_date_rule')->load($form_state->getValue('rrule'));
    $instanceController = new Instances($this->entityTypeManager, $this->formBuilder);
    $instanceController->setSmartDateRule($rrule);
    $instanceController->setUseAjax(TRUE);
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#manage-instances', $instanceController->listInstancesOutput()));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->override($form_state);
    if (!isset($form['ajaxcancel'])) {
      /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
      $entityTypeManager = $this->entityTypeManager;
      /** @var \Drupal\smart_date_recur\Entity\SmartDateRule $rrule */
      $rrule = $entityTypeManager->getStorage('smart_date_rule')->load($form_state->getValue('rrule'));
      $instanceController = new Instances($this->entityTypeManager, $this->formBuilder);
      // Force refresh of parent entity.
      $instanceController->applyChanges($rrule);
      // Output message about operation performed, if not using AJAX.
      $this->messenger()->addMessage($this->t('The instance has been rescheduled.'));
    }
    // Redirect to rrule instance listing.
    $form_state->setRedirect('smart_date_recur.instances', [
      'rrule' => $form_state->getValue('rrule'),
    ],
    );
  }

  /**
   * Create or updating an override entity, this means overriding one rule item.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The provided form values.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function override(FormStateInterface $form_state) {
    if (!empty($form_state->getValue('oid'))) {
      // Existing override, so retrieve and update values.
      $override = SmartDateOverride::load($form_state->getValue('oid'));
      // Only the values, end_value, and duration are changeable.
      $override->set('value', $form_state->getValue('value')->getTimestamp());
      $override->set('end_value', $form_state->getValue('end_value')->getTimestamp());
      $override->set('duration', $form_state->getValue('duration'));
    }
    else {
      $values = [
        'rrule' => $form_state->getValue('rrule'),
        'rrule_index' => $form_state->getValue('rrule_index'),
        'value' => $form_state->getValue('value')->getTimestamp(),
        'end_value' => $form_state->getValue('end_value')->getTimestamp(),
        'duration' => $form_state->getValue('duration'),
      ];
      // New override, so construct object.
      $override = SmartDateOverride::create($values);
    }
    $override->save();
  }

}
