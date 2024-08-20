<?php

namespace Drupal\va_gov_form_builder\Plugin\Field\FieldWidget;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;

/**
 * Plugin implementation of the 'multistep_paragraph' widget.
 *
 * @FieldWidget(
 *   id = "multistep_paragraph",
 *   label = @Translation("Multi-Step Paragraph"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class MultiStepParagraphWidget extends ParagraphsWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'title' => t('Step'),
      'title_plural' => t('Steps'),
      'edit_mode' => 'open',
      'closed_mode' => 'summary',
      'autocollapse' => 'true',
      'closed_mode_threshold' => 0,
      'add_mode' => 'modal',
      'form_display_mode' => 'default',
      'default_paragraph_type' => 'digital_form_name_and_date_of_bi',
      'paragraphs_browser' => 'digital_forms',
      'features' => ['duplicate' => 'duplicate', 'collapse_edit_all' => 'collapse_edit_all'],
    ] + parent::defaultSettings();
  }

  public static function renderNameAndDobWidget($title, $include_dob) {
    $new_paragraph = Paragraph::create([
      'type' => 'digital_form_name_and_date_of_bi',
      'field_title' => $title,
      'field_include_date_of_birth' => $include_dob,
    ]);

    $paragraph_form = \Drupal::service('entity.form_builder')
      ->getForm($new_paragraph, 'default');

    // Remove the 'submit' button from the paragraph form.
    if (isset($paragraph_form['actions']['submit'])) {
      unset($paragraph_form['actions']['submit']);
    }

    $subform = [
      'wrapper' => [
        '#type' => 'container'
      ],
    ];

    $subform['wrapper']['summary_display'] = [
      '#markup' => '<div>Title: ' . $title . '</div><div>Include DOB: ' . ($include_dob ? 'yes' : 'no') . '</div>',
    ];

    // $subform['wrapper']['field_title'] = [
    //   '#type' => 'hidden',
    //   '#value' => $title,
    // ];

    // $subform['wrapper']['field_include_date_of_birth'] = [
    //   '#type' => 'hidden',
    //   '#value' => $include_dob,
    // ];

    // $subform['wrapper']['paragraph_form'] = $paragraph_form;

    return $subform;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $item = $items[$delta];
    $value = $item->getValue();
    $target_id = $value['target_id'];
    $target_revision_id = $value['target_revision_id'];

    $paragraph = \Drupal::service('entity_type.manager')->getStorage('paragraph')->loadRevision($target_revision_id);
    if ($paragraph && $paragraph->id() == $target_id) {
      $title = $paragraph->get('field_title')->value;
      $include_dob = $paragraph->get('field_include_date_of_birth')->value;

      return self::renderNameAndDobWidget($title, $include_dob);
    }

    return $element;

    // $element['instructions'] = [
    //   '#markup' => $this->t('Use the "Manage Paragraphs" link to add or edit paragraphs.'),
    // ];
    // $element['manage_paragraphs'] = [
    //   '#type' => 'link',
    //   '#title' => $this->t('Manage Paragraphs'),
    //   '#url' => \Drupal\Core\Url::fromRoute('your_module.manage_paragraphs', ['node' => $form['#entity']->id()]),
    //   '#attributes' => ['class' => ['button']],
    // ];
    // return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    // Call the parent method to get the existing elements.
    $elements = parent::formMultipleElements($items, $form, $form_state);

    $elements['add_more'] = [
      '#type' => 'markup',
      '#markup' => '<div class="step-add-link"><a class="button" href="/node/add/digital_form/step/add/choose-type">' . t('Add Step') . '</a></div>',
    ];

    return $elements;
  }

}
