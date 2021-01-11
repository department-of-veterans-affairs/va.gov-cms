<?php

namespace Drupal\danse_content_moderation\Plugin\Danse;

use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\danse\Entity\EventInterface;
use Drupal\danse\PayloadInterface;
use Drupal\danse\PluginBase;
use Drupal\danse_content_moderation\Payload;

/**
 * Plugin implementation of DANSE.
 *
 * @Danse(
 *   id = "content_moderation",
 *   label = @Translation("Content Moderation"),
 *   description = @Translation("Provides Content Moderation integration for DANSE.")
 * )
 */
class ContentModeration extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function assertPayload(PayloadInterface $payload): bool {
    return $payload instanceof Payload;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedSubscriptions($roles): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(&$form, FormStateInterface $form_state) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getRedirectUrl(EventInterface $event): Url {
    /** @var \Drupal\danse_content\Payload $payload */
    $payload = $event->getPayload();
    try {
      return $payload->getEntity()->toUrl();
    } catch (EntityMalformedException $e) {
      return Url::fromRoute('<front>');
    }
  }

  public function createContentModerationEvent(Payload $payload) {
    /** @var \Drupal\content_moderation\Entity\ContentModerationState $entity */
    $entity = $payload->getEntity();
    $topic = $entity->moderation_state->value;
    $parent_rev_id = $entity->content_entity_revision_id->value;
    $parent_entity_type = $entity->content_entity_type_id->value;
    $revision = \Drupal::entityTypeManager()->getStorage($parent_entity_type)->loadRevision($parent_rev_id);

    $label = $revision->label() . ' transitioned to ' . $topic;
    return $this->createEvent($topic, $label, $payload);
  }
}
