<?php

use Drupal\block\BlockInterface;

/**
 * Implements hook_ENTITY_TYPE_insert().
 */
function headless_lightning_block_insert(BlockInterface $block) {
  if ($block->id() == 'seven_login') {
    $block->delete();
  }
}
