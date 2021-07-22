<?php

namespace Drupal\va_gov_backend\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Populates attributes in node links to trigger Linkit and prevent dead links.
 *
 * This is intended to be used before, and work with, the Linkit filter.
 *
 * @Filter(
 *   id = "va_gov_backend_node_link_enforcement",
 *   title = @Translation("Node Link Format Enforcement"),
 *   description = @Translation("Populates attributes in node links to prevent dead links."),
 *   settings = {
 *     "title" = TRUE,
 *   },
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class NodeLinkEnforcementFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a Node Link Enforcement Filter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    if (strpos($text, '<a href="/node/') === FALSE) {
      return $result;
    }
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a[starts-with(@href, "/node/") and ( not(@data-entity-type) or not(@data-entity-uuid) )]') as $element) {
      $path = $element->getAttribute('href');
      if (preg_match('#node/(\d+)#', $path, $matches)) {
        $node = $this->entityTypeManager->getStorage('node')->load($matches[1]);
        if ($node) {
          $element->setAttribute('data-entity-type', 'node');
          $element->setAttribute('data-entity-substitution', 'canonical');
          $element->setAttribute('data-entity-uuid', $node->uuid());
        }
      }
    }
    $result->setProcessedText(Html::serialize($dom));
    return $result;
  }

}
