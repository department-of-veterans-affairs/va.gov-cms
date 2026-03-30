<?php

/**
 * @file
 * Post-update hooks for VA.gov Notifications.
 */

/**
 * Installs the product owner contact config entity type.
 */
function va_gov_notifications_post_update_install_product_owner_contact_entity_type(): string {
  $entity_type_manager = \Drupal::entityTypeManager();
  $entity_type = $entity_type_manager->getDefinition('product_owner_contact');
  \Drupal::entityDefinitionUpdateManager()->installEntityType($entity_type);

  return 'Installed the product_owner_contact entity type.';
}

/**
 * Ensures the product owner contact config entity type is installed.
 */
function va_gov_notifications_post_update_ensure_product_owner_contact_entity_type_installed(): string {
  $change_list = \Drupal::entityDefinitionUpdateManager()->getChangeList();
  if (!isset($change_list['product_owner_contact'])) {
    return 'The product_owner_contact entity type is already installed.';
  }

  $entity_type_manager = \Drupal::entityTypeManager();
  $entity_type = $entity_type_manager->getDefinition('product_owner_contact');
  \Drupal::entityDefinitionUpdateManager()->installEntityType($entity_type);

  return 'Installed the product_owner_contact entity type from follow-up update.';
}
