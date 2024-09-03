<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

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

  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL, $step_type = NULL) {
    $this->setStepType($step_type);

    $form['#title'] = $this->t('Digital Form - Add Step');

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $node->id(),
    ];

    $form['step_type'] = [
      '#type' => 'hidden',
      '#value' => $step_type,
    ];

    if ($step_type === 'digital_form_name_and_date_of_bi') {
      $form['title'] = [
        '#type' => 'textfield',
        '#title' => 'Title',
        '#default_value' => 'Name and Date of Birth',
      ];
      $form['include_dob'] = [
        '#type' => 'checkbox',
        '#title' => 'Include Date of Birth?',
      ];
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nid = $form_state->getValue('nid');
    $step_type = $form_state->getValue('step_type');
    $title = $form_state->getValue('title');
    $include_dob = $form_state->getValue('include_dob');

    if ($step_type === 'digital_form_name_and_date_of_bi') {
      $new_paragraph = Paragraph::create([
        'type' => $this->step_type,
        'field_title' => $title,
        'field_include_date_of_birth' => $include_dob,
      ]);
      $new_paragraph->save();

      $node = Node::load($nid);
      $paragraphs = $node->get('field_chapters')->referencedEntities();
      $paragraphs[] = $new_paragraph;
      $node->set('field_chapters', $paragraphs);
      $node->save();
      \Drupal::messenger()->addMessage($this->t('Step added'));
    }

    $form_state->setRedirect('va_gov_form_builder.digital_form.edit.add_step.yes_no', [
      'nid' => $nid,
    ]);
  }
}
