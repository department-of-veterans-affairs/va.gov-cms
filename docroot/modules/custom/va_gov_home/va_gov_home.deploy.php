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
