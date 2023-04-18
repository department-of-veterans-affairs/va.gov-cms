<?php

namespace Drupal\va_gov_entity_browser\Plugin\EntityBrowser\FieldWidgetDisplay;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\Plugin\EntityBrowser\FieldWidgetDisplay\EntityLabel;

/**
 * Displays a link to the entity.
 *
 * @EntityBrowserFieldWidgetDisplay(
 *   id = "linked_title",
 *   label = @Translation("Linked title"),
 *   description = @Translation("Displays entity with a link.")
 * )
 */
class LinkedTitle extends EntityLabel {

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function view(EntityInterface $entity) {
    $config = $this->getConfiguration();
    $translation = $this->entityRepository->getTranslationFromContext($entity);

    // Display a linked title only if user has access to view the label and
    // entity.
    if ($translation->access('view label') && $translation->access('view') && $entity->hasLinkTemplate('canonical')) {
      $return = [
        '#title' => $entity->label(),
        '#type' => 'link',
        '#url' => $entity->toUrl(),
      ];
      if (!empty($config['target_blank'])) {
        $return['#attributes'] = [
          'target' => '_blank',
          'rel' => 'noopener noreferrer',
        ];
      }
      return $return;
    }

    return parent::view($entity);
  }

  /**
   * {@inheritDoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form['target_blank'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open in new tab'),
      '#default_value' => $config['target_blank'] ?: 0,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'target_blank' => 1,
    ] + parent::defaultConfiguration();
  }

}
