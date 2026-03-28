<?php

namespace Drupal\va_gov_resources_and_support\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\feature_toggle\FeatureStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Resources and Support Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * Feature Toggle status service.
   *
   * @var \Drupal\feature_toggle\FeatureStatus
   */
  private FeatureStatus $featureStatus;

  /**
   * Constructs an EntityEventSubscriber object.
   *
   * @param \Drupal\feature_toggle\FeatureStatus $feature_status
   *   The Feature Status service.
   */
  public function __construct(FeatureStatus $feature_status) {
    $this->featureStatus = $feature_status;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_node_outreach_asset_form.alter' => 'alterOutreachAssetNodeForm',
      'hook_event_dispatcher.form_node_outreach_asset_edit_form.alter' => 'alterOutreachAssetNodeForm',
    ];
  }

  /**
   * Alter the outreach_asset node add/edit form.
   *
   * Hides field_lc_categories when the outreach materials topics feature
   * toggle is enabled, allowing the cutover to field_outreach_materials_topics.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The form event.
   */
  public function alterOutreachAssetNodeForm(FormIdAlterEvent $event): void {
    $this->hideLcCategoriesFieldByToggle($event->getForm());
  }

  /**
   * Hides field_lc_categories when the feature toggle is enabled.
   *
   * @param array $form
   *   The form render array, passed by reference.
   */
  private function hideLcCategoriesFieldByToggle(array &$form): void {
    $status = $this->featureStatus->getStatus('feature_outreach_materials_topics');
    if ($status && isset($form['field_lc_categories'])) {
      $form['field_lc_categories']['#access'] = FALSE;
    }
  }

}
