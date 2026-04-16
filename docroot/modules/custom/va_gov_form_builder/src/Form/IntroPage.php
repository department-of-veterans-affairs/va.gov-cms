<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderFormBase;

/**
 * Form step for entering a form's name and other basic info.
 *
 * Other basic info includes form number, OMB info, etc.
 */
class IntroPage extends FormBuilderFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__intro';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL) {
    $this->allowEmptyDigitalForm = FALSE;
    $this->isCreate = FALSE;
    $form = parent::buildForm($form, $form_state, $digitalForm);

    $form['#theme'] = 'form__va_gov_form_builder__intro';

    $form['intro_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Intro paragraph'),
      '#required' => TRUE,
      '#description' => '<ul>
        <li>Add a brief intro describing when to use this form. This could be 1 to 3 sentences, with no more than 25 words per sentence.</li>
        <li>Try reusing the "when to use" blurb from the form detail page in Find a Form. For example, using the Veterans Pension blurb from the form detail page, this intro could be "Use this form if you\'re a wartime Veteran and want to file a pension claim."</li>
      </ul>',
      '#default_value' => $this->getDigitalFormFieldValue('field_intro_text'),
    ];

    $form['what_to_know'] = [
      '#type' => '#container',
      '#tree' => TRUE,
    ];

    $bullets = $this->digitalForm->get('field_digital_form_what_to_know')->getValue();
    for ($i = 0; $i < 5; $i++) {
      $form['what_to_know'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t("Bullet point @count", ['@count' => $i + 1]),
        '#default_value' => $bullets[$i] ?? '',
      ];
    }

    $form['actions']['save_and_continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
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

    $form['preview'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => self::IMAGE_DIR . 'introduction.png',
        'alt' => $this->t('A preview of the introduction page.'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function setDigitalFormFromFormState(FormStateInterface $form_state) {
    $introText = $form_state->getValue('intro_text');
    $whatToKnow = $form_state->getValue('what_to_know');

    $this->digitalForm->set('field_intro_text', $introText);
    $this->digitalForm->set('field_digital_form_what_to_know', $whatToKnow);
  }

  /**
   * {@inheritdoc}
   */
  protected function validateDigitalForm(array $form, FormStateInterface $form_state) {
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->digitalForm->validate();

    if ($violations->count() > 0) {
      self::setFormErrors($form_state, $violations, [
        'field_intro_text' => $form['intro_text'],
        'field_digital_form_what_to_know' => $form['what_to_know'],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $form_state->setRedirect('va_gov_form_builder.layout', [
      'nid' => $this->digitalForm->id(),
    ]);
  }

}
