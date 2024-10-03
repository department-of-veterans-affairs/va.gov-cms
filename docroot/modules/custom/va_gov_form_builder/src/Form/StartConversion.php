<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form step for starting a new form conversion.
 */
class StartConversion extends FormBase {
  private const FIELD_NAMES = [
    'title',
    'field_va_form_number',
    'field_omb_number',
    'field_respondent_burden',
    'field_expiration_date',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The Digital Form node set for creation by this form.
   *
   * @var \Drupal\node\Entity\Node
   */
  private $digitalFormNode;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__start_conversion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Form Builder');

    $form['start_new_conversion_header'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#children' => $this->t('Start a new conversion'),
    ];

    $form['help_text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#children' => $this->t('Begin your transformation of this form by including this information to start.
        Refer to your existing form to copy this information over.'),
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Name'),
      '#description' => $this->t('Insert the form name'),
      '#required' => TRUE,
    ];

    $form['field_va_form_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Number'),
      '#description' => $this->t('Insert the form number'),
      '#required' => TRUE,
    ];

    $form['omb_header'] = [
      '#type' => 'item',
      '#title' => $this->t('OMB information'),
      '#description' => $this->t('Refer to the form'),
    ];

    $form['field_omb_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OMB number'),
      '#description' => $this->t('Insert the OMB number (format: xxxx-xxxx)'),
      '#required' => TRUE,
    ];

    $form['field_respondent_burden'] = [
      '#type' => 'number',
      '#title' => $this->t('Respondent burden'),
      '#description' => $this->t('Number of minutes as indicated on the form'),
      '#required' => TRUE,
    ];

    $form['field_expiration_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Expiration date'),
      '#description' => $this->t('Form expiration date as indicated on the form'),
      '#required' => TRUE,
    ];

    $form['actions']['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#attributes' => [
        'class' => [
          'button',
          'button--primary',
          'js-form-submit',
          'form-submit',
        ],
      ],
      '#weight' => '10',
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#weight' => '20',
      '#submit' => ['::backButtonSubmitHandler'],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  /**
   * Creates a Digital Form node from the form-state data.
   */
  private function createDigitalFormNode(array &$form, FormStateInterface $form_state) {
    $this->digitalFormNode = Node::create([
      'type' => 'digital_form',
      'title' => $form_state->getValue('title'),
      'field_va_form_number' => $form_state->getValue('field_va_form_number'),
      'field_omb_number' => $form_state->getValue('field_omb_number'),
      'field_respondent_burden' => $form_state->getValue('field_respondent_burden'),
      'field_expiration_date' => $form_state->getValue('field_expiration_date'),
    ]);
  }

  /**
   * Submit handler for the 'Back' button.
   */
  public function backButtonSubmitHandler(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('va_gov_form_builder.intro');
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->createDigitalFormNode($form, $form_state);

    // Validate the node entity.
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->digitalFormNode->validate();

    // Loop through each violation and set errors on the form.
    if ($violations->count() > 0) {
      foreach ($violations as $violation) {
        $fieldName = $violation->getPropertyPath();

        // Only concern ourselves with validation of fields used on this form.
        if (in_array($fieldName, self::FIELD_NAMES)) {
          $message = $violation->getMessage();
          $form_state->setErrorByName($fieldName, $message);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the previously validated node.
    $this->digitalFormNode->save();

    // For now, redirect to the default node-edit form
    // to confirm creation of the node.
    $form_state->setRedirect('entity.node.edit_form', [
      'node' => $this->digitalFormNode->id(),
    ]);
  }

}
