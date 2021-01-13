<?php

namespace Drupal\danse_content_moderation;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\danse\Entity\Event;
use Drupal\danse\Entity\EventInterface;
use Drupal\danse\PayloadBase;
use Drupal\danse\PayloadInterface;
use Drupal\user\Entity\User;

/**
 * Content moderation payload.
 */
final class Payload extends PayloadBase {

  /**
   * The payload label.
   *
   * @var string
   */
  private $label;

  /**
   * The moderated entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * Content constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The moderated entity.
   */
  public function __construct(ContentEntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return $this->entity->label() ?? '';
  }

  /**
   * Generate the payload label.
   *
   * @return string
   *   The label.
   */
  protected function generateLabel() {
    $label = '';
    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventReference(): string {
    return implode('-', [
      $this->entity->getEntityTypeId(),
      $this->entity->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getSubscriptionReference(EventInterface $event): string {
    return implode('-', [
      $event->getPluginId(),
      $this->entity->getEntityTypeId(),
      $this->entity->bundle(), $event->getTopic(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareArray(): array {
    return [
      'entity' => [
        'type' => $this->entity->getEntityTypeId(),
        'bundle' => $this->entity->bundle(),
        'id' => $this->entity->id(),
        'label' => $this->entity->label(),
        'uid' => $this->entity->getOwnerId(),
      ],
      'label' => $this->generateLabel(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $payload): PayloadInterface {
    try {
      $storage = Drupal::entityTypeManager()->getStorage($payload['entity']['type']);
    }
    catch (InvalidPluginDefinitionException $e) {
    }
    catch (PluginNotFoundException $e) {
    }

    /* @noinspection IsEmptyFunctionUsageInspection */
    if (empty($storage)) {
      // Entity type does not exist anymore, create
      // a dummy entity and don't save it.
      $entity = Event::create([
        'label' => 'Content type ' . $payload['entity']['type'] . ' no longer exists.',
      ]);
    }
    else {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $entity = $storage->load($payload['entity']['id']);

      if (empty($entity)) {
        // Entity got deleted, created a dummy entity and don't save it.
        $entity = $storage->create([
          $storage->getEntityType()->getKey('bundle') => $payload['entity']['bundle'],
          $storage->getEntityType()->getKey('id') => $payload['entity']['id'],
          $storage->getEntityType()->getKey('label') => $payload['entity']['label'],
        ]);
      }
    }
    return new static($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(): ContentEntityInterface {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function hasAccess($uid): bool {
    /** @var \Drupal\user\UserInterface $user */
    $user = User::load($uid);
    return $this->getEntity()->access('view', $user);
  }

  /**
   * Create entity class from entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The moderated entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The moderated entity.
   */
  public static function createFromEntity(ContentEntityInterface $entity) {
    return new static($entity);
  }

}
