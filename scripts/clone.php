<?php

// @codingStandardsIgnoreFile

use Drupal\entity_clone\Event\EntityCloneEvent;
use Drupal\entity_clone\Event\EntityCloneEvents;

echo 'Loading content' . PHP_EOL;
//$content = views_get_view_result('taxonomy_term', 'page_1', 'visn-2');
$state = \Drupal::getContainer()->get('state');
$state->set('va_gov.content_export_enable', FALSE);

$query = \Drupal::entityQuery('node');
// VISN 4 and VISN 2
$query->condition('field_administration.target_id', [247, 251, 248, 252, 253, 254, 255, 249, 250, 182, 185, 184, 183, 180, 181, 12, 186, 188], 'IN');
$query->range(0, 10000);
$entity_ids = $query->execute();

$eventDispatcher = \Drupal::getContainer()->get('event_dispatcher');
foreach ($entity_ids as $id) {
  $entity_clone_handler = \Drupal::entityTypeManager()->getHandler('node', 'entity_clone');
  if (\Drupal::entityTypeManager()->hasHandler('node', 'entity_clone_form')) {
    $entity_clone_form_handler = \Drupal::entityTypeManager()->getHandler('node', 'entity_clone_form');
  }

  echo 'Cloning entity ' . $id . PHP_EOL;
  $entity = \Drupal::entityTypeManager()->getStorage('node')->load($id);

  $duplicate = $entity->createDuplicate();
  $eventDispatcher->dispatch(EntityCloneEvents::PRE_CLONE, new EntityCloneEvent($entity, $duplicate));
  $cloned_entity = $entity_clone_handler->cloneEntity($entity, $duplicate);
  $cloned_entity->setPublished();
  $eventDispatcher->dispatch(EntityCloneEvents::POST_CLONE, new EntityCloneEvent($entity, $duplicate));
  echo "Entity Cloned" . PHP_EOL;
}
