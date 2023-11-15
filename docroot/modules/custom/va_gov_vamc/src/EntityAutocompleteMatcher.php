<?php

namespace Drupal\va_gov_vamc;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityAutocompleteMatcherInterface;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Matcher class to get autocompletion results for entity reference.
 */
class EntityAutocompleteMatcher implements EntityAutocompleteMatcherInterface {

  /**
   * The Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   *   The Entity repository service.
   */
  private $entityRepository;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * The entity reference selection handler plugin manager.
   *
   * @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface
   */
  protected $selectionManager;

  /**
   * Constructs an EntityAutocompleteMatcher object.
   *
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity reference selection handler plugin manager.
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_manager
   *   The entity reference selection handler plugin manager.
   */
  public function __construct(EntityRepository $entity_repository, EntityTypeManager $entity_type_manager, SelectionPluginManagerInterface $selection_manager) {
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->selectionManager = $selection_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {
    $matches = [];

    $options = $selection_settings + [
      'target_type' => $target_type,
      'handler' => $selection_handler,
    ];
    $handler = $this->selectionManager->getInstance($options);

    // Get an array of matching entities.
    $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
    $match_limit = isset($selection_settings['match_limit']) ? (int) $selection_settings['match_limit'] : 10;
    $entity_labels = !empty($handler) ? $handler->getReferenceableEntities($string, $match_operator, $match_limit) : [];
    $entity_repository = $this->entityRepository;
    // Loop through the entities and convert them into autocomplete output.
    foreach ($entity_labels as $values) {
      foreach ($values as $entity_id => $label) {
        $entity_loader = $this->entityTypeManager->getStorage($target_type)->load($entity_id);
        /** @var \Drupal\node\Entity\Node $entity */
        $entity = $entity_repository->getTranslationFromContext($entity_loader);
        $system = NULL;
        if ($target_type === 'node' && $entity->bundle() === 'health_care_local_facility') {
          $tid = $entity->field_administration->getString();
          /** @var \Drupal\taxonomy\Entity\Term $term */
          $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
          $system = ' - ' . $term->getName();
        }
        $key = $label . ' (' . $entity_id . ')';
        // Strip starting/trailing white spaces, line breaks and tags.
        $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
        // Names containing commas or quotes must be wrapped in quotes.
        $key = Tags::encode($key);
        $label = $label . ' (' . $entity_id . ')' . $system;
        $matches[] = ['value' => $key, 'label' => $label];
      }
    }

    return $matches;
  }

}
