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

final class Payload extends PayloadBase {

  private $label;

  /**
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * Content constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
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

  protected function generateLabel() {

  }

  /**
   * {@inheritdoc}
   */
  public function getEventReference(): string {
    return implode('-', [$this->entity->getEntityTypeId(), $this->entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getSubscriptionReference(EventInterface $event): string {
    return implode('-', [$event->getPluginId(), $this->entity->getEntityTypeId(), $this->entity->bundle(), $event->getTopic()]);
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
    /** @noinspection IsEmptyFunctionUsageInspection */
    if (empty($storage)) {
      // Entity type does not exist anymore, created a dummy entity and don't save it.
      $entity = Event::create([
        'label' => 'Content type '. $payload['entity']['type'] . ' no longer exists.',
      ]);
    }
    else {
      /** @var ContentEntityInterface $entity */
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

  public static function createFromEntity(ContentEntityInterface $entity) {
    return new static($entity);
  }
}
