<?php

namespace Drupal\va_gov_migrate\Plugin\Derivative;

use Drupal\migrate\Plugin\Derivative\MigrateEntity;
use Drupal\va_gov_migrate\Plugin\migrate\destination\EntityPreserveDraft;

/**
 * Deriver for entity_preserve_draft:ENTITY_TYPE migrations.
 */
class MigrateEntityPreserveDraft extends MigrateEntity {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->entityDefinitions as $entity_type => $entity_info) {
      $this->derivatives[$entity_type] = [
        'id' => "entity_preserve_draft:$entity_type",
        'class' => EntityPreserveDraft::class,
        'requirements_met' => 1,
        'provider' => $entity_info->getProvider(),
      ];
    }
    return $this->derivatives;
  }

}
