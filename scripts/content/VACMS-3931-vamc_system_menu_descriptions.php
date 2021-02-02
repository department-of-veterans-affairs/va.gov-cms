<?php

/**
 * @file
 * Helper script - outputs sed command to update VAMC System Menu descriptions.
 */

$query = \Drupal::entityQuery('node')
  ->condition('type', 'health_care_region_page');
$results = $query->execute();

$menu_link_manager = \Drupal::service('plugin.manager.menu.link');
$entity_type_manager = \Drupal::entityTypeManager();

foreach ($results as $result) {
  $node = node_load($result);

  $visn = '';
  $owner_term = $node->get('field_administration')->referencedEntities()[0];
  if (!empty($owner_term)) {
    $parents = array_values($entity_type_manager
      ->getStorage('taxonomy_term')
      ->loadAllParents($owner_term->id())
    );

    if (!empty($parents[1])) {
      $visn = $parents[1]->get('name')->getString();
    }
  }

  $alias = '';
  $alias = \Drupal::service('path.alias_manager')->getAliasByPath("/node/{$node->id()}", 'en');

  $description = "{$visn} | va.gov{$alias}";
  $stub = str_replace('-health-care', '', $alias);
  $stub = str_replace('/', '', $stub);
  $command = "sed -i \"s#^description: .*#description: '{$description}'#\" system.menu.*{$stub}*.yml";

  print_r($command);
  print_r("\n");
}
