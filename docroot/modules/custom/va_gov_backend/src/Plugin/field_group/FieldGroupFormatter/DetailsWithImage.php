<?php

namespace Drupal\va_gov_backend\Plugin\field_group\FieldGroupFormatter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field_group\Plugin\field_group\FieldGroupFormatter\Details;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Details with image field group formatter.
 *
 * @FieldGroupFormatter(
 *   id = "detailswithimage",
 *   label = @Translation("Details with image"),
 *   description = @Translation("Adds a details with image field group"),
 *   supported_contexts = {
 *     "form",
 *     "view"
 *   }
 * )
 */
class DetailsWithImage extends Details implements ContainerFactoryPluginInterface {
  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['group'],
      $configuration['settings'],
      $configuration['label'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * Constructs a FieldGroupFormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param object $group
   *   The group object.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   The entity instance.
   */
  public function __construct($plugin_id, $plugin_definition, \stdClass $group, array $settings, $label, EntityTypeManagerInterface $entityManager) {
    parent::__construct($plugin_id, $plugin_definition, $group, $settings, $label);
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function process(&$element, $processed_object) {
    parent::process($element, $processed_object);
    if (!empty($this->getSetting('visual_guide_file_name'))) {
      $path = \Drupal::service('module_handler')->getModule('va_gov_backend')->getPath() . '/images/' . $this->getSetting('visual_guide_file_name');
      if (file_exists($path)) {
        $svg_render = [
          '#theme' => 'image',
          '#uri' => $path,
          '#attributes' => [
            'alt' => !empty($this->getSetting('visual_guide_alt_text')) ? $this->getSetting('visual_guide_alt_text') : '',
            'class' => [
              'visual-layout-svg',
            ],
          ],
        ];

        $element += [
          '#markup' => render($svg_render),
        ];

      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);
    $this->process($element, $rendering_object);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $form['visual_guide_file_name'] = [
      '#title' => $this->t('Visual guide file name'),
      '#type' => 'textarea',
      '#description' => $this->t('SVG image to help users understand panel layout'),
      '#rows' => 1,
      '#default_value' => $this->getSetting('visual_guide_file_name'),
    ];

    $form['visual_guide_alt_text'] = [
      '#title' => $this->t('Visual guide alt text'),
      '#type' => 'textarea',
      '#description' => $this->t('SVG image alt text for screenreaders'),
      '#rows' => 1,
      '#default_value' => $this->getSetting('visual_guide_alt_text'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = [];
    if ($this->getSetting('open')) {
      $summary[] = $this->t('Default state open');
    }
    else {
      $summary[] = $this->t('Default state closed');
    }

    if ($this->getSetting('required_fields')) {
      $summary[] = $this->t('Mark as required');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultContextSettings($context) {
    $defaults = [
      'open' => FALSE,
      'required_fields' => $context == 'form',
    ] + parent::defaultSettings($context);

    if ($context == 'form') {
      $defaults['required_fields'] = 1;
    }

    return $defaults;
  }

}
