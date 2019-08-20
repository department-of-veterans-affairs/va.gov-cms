<?php

namespace CustomDrupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;

/**
 * Provides content model step definitions for Behat.
 */
class ContentModelContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @Then exactly the following auto labels should be configured
   * @throws \Exception
   */
  public function assertAutoLabels(TableNode $expected) {
    $config = \Drupal::config('auto_entitylabel.settings')->get();
    $auto_label_info = [];
    foreach ($config as $key => $value) {
      $key_suffix = '_pattern';
      if (substr($key, -8) === $key_suffix) {
        $entity_types = [
          'node_type',
          'taxonomy_vocabulary',
        ];
        foreach ($entity_types as $entity_type_id) {
          $key_prefix = "{$entity_type_id}_";
          if (strpos($key, $key_prefix) === 0) {
            $id = substr($key, strlen($key_prefix), -strlen($key_suffix));
            /** @var \Drupal\Core\Entity\EntityInterface $entity_type */
            $entity_type = \Drupal::entityTypeManager()
              ->getStorage($entity_type_id)
              ->load($id);
            if ($entity_type) {
              $auto_label_info[] = [
                (string) $entity_type->getEntityType()->getLabel(),
                $entity_type->label(),
                $value,
              ];
            }
          }
        }
      }
    }
    $actual = new TableNode($auto_label_info);

    (new TableEqualityAssertion($expected, $actual))
      ->expectHeader([
        'type',
        'bundle',
        'pattern',
      ])
      ->ignoreRowOrder()
      ->setMissingRowsLabel('Missing patterns')
      ->setUnexpectedRowsLabel('Unexpected patterns')
      ->assert();
  }

}
