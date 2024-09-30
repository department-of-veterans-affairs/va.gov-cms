<?php

/**
 * @file
 * Script to create test content blocks for VACMS-19077.
 */

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Datetime\DrupalDateTime;

$base_entity = [
  'info' => 'Expirable Content Test Block',
  'type' => 'news_promo',
  'langcode' => 'en',
  'status' => 1,
  'moderation_state' => 'published',
  'reusable' => 1,
  'uid' => 1,
];

$base_date = DrupalDateTime::createFromTimestamp(time());
$warn_node = clone $base_date;
$exp_node = clone $base_date;

$entities = [
  'not expired or warn' => $base_date->getTimestamp(),
  'warn' => $warn_node->sub(new \DateInterval('P13D'))->getTimestamp(),
  'expired' => $exp_node->sub(new \DateInterval('P15D'))->getTimestamp(),
];

foreach ($entities as $key => $date) {
  $block = BlockContent::create($base_entity);
  $block->setChangedTime($date);
  $block->setInfo($base_entity['info'] . ':' . $key);
  $block->setPublished();
  $block->save();
}
