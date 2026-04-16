<?php

/**
 * @file
 * Script to create test content for VACMS-15506.
 */

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;

$node_base = [
  'title' => 'Expirable Content Test Node',
  'status' => 1,
  'moderation_state' => 'published',
  'type' => 'banner',
  'uid' => 1,
  'revision_default' => 1,
  'field_administration' => ['target_id' => 194],
];

$base_date = DrupalDateTime::createFromTimestamp(time());
$warn_node = clone $base_date;
$exp_node = clone $base_date;

$dates = [
  'not expired or warn' => $base_date->getTimestamp(),
  'warn' => $warn_node->sub(new \DateInterval('P5D'))->getTimestamp(),
  'expired' => $exp_node->sub(new \DateInterval('P8D'))->getTimestamp(),
];

foreach ($dates as $key => $date) {
  $node = Node::create($node_base);
  $node->set('field_last_saved_by_an_editor', $date);
  $node->set('title', $node->getTitle() . ':' . $key);
  $node->save();
}
