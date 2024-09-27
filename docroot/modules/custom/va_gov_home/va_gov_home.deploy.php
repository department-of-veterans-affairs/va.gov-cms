<?php

/**
 * @file
 * Deploy hooks for va_gov_home.
 *
 * This is a NAME.deploy.php file. It contains "deploy" functions. These are
 * one-time functions that run *after* config is imported during a deployment.
 * These are a higher level alternative to hook_update_n and
 * hook_post_update_NAME functions.
 *
 * See https://www.drush.org/latest/deploycommand/#authoring-update-functions
 * for a detailed comparison.
 */

use Drupal\block_content\Entity\BlockContent;
use Drupal\entityqueue\Entity\EntityQueue;
use Drupal\entityqueue\Entity\EntitySubqueue;
use Drupal\expirable_content\EntityOperations;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;

/**
 * Creates hub menu links.
 *
 * @throws \Drupal\Core\Entity\EntityMalformedException
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function va_gov_home_deploy_create_hub_menu_links() {
  // List of existing Benefit Hub Landing Page node IDs, in the order we want
  // them in the home-page-hub-list menu.
  // 67 = Health Care
  // 68 = Disability
  // 71 = Education and Training
  // 72 = Careers and employment
  // 73 = Pension
  // 74 = Housing assistance
  // 77 = Life insurance
  // 78 = Burials and memorials
  // 79 = Records
  // 809 = Service member benefits
  // 810 = Family member benefits.
  $nids = [
    67,
    68,
    71,
    72,
    73,
    74,
    77,
    78,
    79,
    809,
    810,
  ];

  // Create internal menu links.
  foreach ($nids as $weight => $nid) {
    $node = Node::load($nid);
    $link = MenuLinkContent::create([
      'enabled' => TRUE,
      'title' => $node->field_home_page_hub_label->value,
      'link' => ['uri' => "entity:{$node->toUrl()->getInternalPath()}"],
      'external' => FALSE,
      'menu_name' => 'home-page-hub-list',
      'weight' => $weight,
      'field_icon' => $node->field_title_icon->value,
      'field_link_summary' => $node->field_teaser_text->value,
    ]);
    $link->save();
  }
  // Create VA Department information link as external url.
  $link = MenuLinkContent::create([
    'enabled' => TRUE,
    'title' => 'VA department information',
    'link' => ['uri' => "https://department.va.gov/"],
    'external' => TRUE,
    'menu_name' => 'home-page-hub-list',
    'weight' => 12,
    'field_icon' => 'va-dept-info',
    'field_link_summary' => 'Learn more about the VA departments that manage our benefit and health care programs.',
  ]);
  $link->save();
}

/**
 * Creates new CTA with Links block for Homepage, and adds it to entityqueue.
 *
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function va_gov_home_deploy_create_i_cta_with_links() {
  $block = BlockContent::create([
    'type' => 'cta_with_link',
    'info' => 'Home page create account block',
    'langcode' => 'en',
    'reusable' => TRUE,
    'moderation_state' => 'published',
    'revision_log' => 'Initial creation via Drush deploy hook.',
    'field_administration' => ['target_id' => 1109],
    'field_cta_summary_text' => 'Create an account to manage your VA benefits and care in one place — anytime, from anywhere.',
    'field_primary_cta_button_text' => 'Create account',
    'field_primary_cta_button_url' => ['uri' => 'internal:/<none>'],
    'field_related_info_links' => [
      'uri' => 'entity:node/51915',
      'title' => 'Learn how an account helps you',
    ],
  ]);
  $block->save();

  $queue = EntityQueue::load('v2_home_page_create_account');
  $subQueue = EntitySubqueue::load($queue->id());
  $subQueue->set('items', ['target_id' => $block->id()]);
  $subQueue->save();
}

/**
 * Seed news promo expirable content data.
 */
function va_gov_home_deploy_seed_news_promo(array &$sandbox): void {
  // This deploy hook serves to "seed" the initial expiration dates for
  // VACMS-19077.
  /** @var \Drupal\expirable_content\EntityOperations $entityOperations */
  $entityOperations = \Drupal::service('class_resolver')
    ->getInstanceFromDefinition(EntityOperations::class);
  // We only expect one or two blocks.
  $blocks = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties([
    'type' => 'news_promo',
    'status' => 1,
  ]);
  foreach ($blocks as $block) {
    try {
      $entityOperations->entityInsert($block);
    }
    catch (\Exception $e) {
      \Drupal::logger('va_gov_banner')->error(sprintf('Could not create new Expirable Content entity for block id: %bid. The error was: <pre> %error</pre>',
        $block->id(),
        $e,
      ));
    }
  }
}
