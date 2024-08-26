<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddStep extends FormBase {

  private $step_type;
  private $step_fields;
  private $step_form_id;

  /**
   * The entity form builder service.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

    /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs an AddStep object.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder service.
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFormBuilder = $entity_form_builder;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container->get('entity_field.manager')
    );
  }

  private function setStepType($step_type) {
    $this->step_type = $step_type;

    $paragraph_all_fields = $this->entityFieldManager->getFieldDefinitions('paragraph', $step_type);
    $paragraph_base_fields = array_keys($this->entityFieldManager->getBaseFieldDefinitions('paragraph'));
    $this->step_fields = array_keys(array_filter($paragraph_all_fields, function ($field) use ($paragraph_base_fields) {
      return !in_array($field->getName(), $paragraph_base_fields);
    }));

    $paragraph = Paragraph::create([
      'type' => $step_type,
    ]);
    $this->step_form_id = $this->entityFormBuilder->getForm($paragraph)['#form_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    if (empty($this->step_type)) {
      return 'digital_form_form__add_step';
    }

    return $this->step_form_id;
  }

  public function buildForm(array $form, FormStateInterface $form_state, $step_type = NULL) {
    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $digital_form_in_progress = $temp_store->get('digital_form_in_progress');

    $form['#title'] = $this->t('Digital Form - Add Step');

    $this->setStepType($step_type);

    $paragraph = Paragraph::create([
      'type' => $step_type,
    ]);
    $paragraph_form = $this->entityFormBuilder->getForm($paragraph);
    // if (isset($paragraph_form['actions'])) {
    //   unset($paragraph_form['actions']);
    // }
    // if(isset($paragraph_form['form_build_id'])) {
    //   unset($paragraph_form['form_build_id']);
    // }
    // if(isset($paragraph_form['form_id'])) {
    //   unset($paragraph_form['form_id']);
    // }
    // if(isset($paragraph_form['form_token'])) {
    //   unset($paragraph_form['form_token']);
    // }
    // foreach ($paragraph_form as $key => $value) {
    //   if (strpos($key, '#') === 0) {
    //     unset ($paragraph_form[$key]);
    //   }
    // }

    if(isset($paragraph_form['#process'])) {
      unset($paragraph_form['#process'][0]);
    }

    // foreach($this->step_fields as $step_field) {
    //   $form['step_fields'][$step_field] = $paragraph_form[$step_field]['widget'];
    //   //$new_paragraph->set($step_field, $form_state->getValue($step_field));
    // }

    // $form['test_radio'] = [
    //   '#type' => 'radios',
    //   '#options' => [
    //     'a' => 'a',
    //     'b' => 'b',
    //   ],
    //   '#title' => $this->t('Test Field'),
    //   '#required' => TRUE,
    // ];

    // $form['step_fields'] = [
    //   '#type' => 'container',
    //   'form' => $paragraph_form,
    // ];

    // $form['field_title2'] = [
    //   '#tree' => TRUE,
    // ];

    // $form['field_title2'][0]['value'] = [
    //   '#type' => 'textfield',
    //   '#title' => 'Test Title',
    //   '#required' => FALSE,
    //   '#tree' => TRUE,
    // ];

    // $form['actions']['submit'] = [
    //   '#type' => 'submit',
    //   '#value' => $this->t('Next'),
    // ];

    // xdebug_var_dump($form['step_fields']['form']);

    xdebug_var_dump($this->getFormId());
    return $paragraph_form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Print the call stack
    // $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    // xdebug_var_dump($backtrace);
    xdebug_var_dump($form_state->getValues());
    xdebug_var_dump($form_state->getUserInput());

    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $digital_form_in_progress = $temp_store->get('digital_form_in_progress');
    $steps = $digital_form_in_progress->get('field_chapters')->getValue();

    $new_paragraph = Paragraph::create([
      'type' => $this->step_type,
    ]);



    foreach($this->step_fields as $step_field) {
      $new_paragraph->set($step_field, $form_state->getValue($step_field));
    }
    exit;
    $steps[] = $new_paragraph;
    $digital_form_in_progress->set('field_chapters', $steps);
    $temp_store->set('digital_form_in_progress', $digital_form_in_progress);

    \Drupal::messenger()->addMessage($this->t('Step added'));
    $form_state->setRedirect('va_gov_form_builder.digital_form_form.add_step.yes_no');
  }
}
