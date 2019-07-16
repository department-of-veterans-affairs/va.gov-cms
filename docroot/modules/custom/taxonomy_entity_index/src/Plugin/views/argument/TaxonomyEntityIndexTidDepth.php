<?php

namespace Drupal\taxonomy_entity_index\Plugin\views\argument;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\taxonomy\Plugin\views\argument\IndexTidDepth;

/**
 * Argument handler for entity taxonomy terms with depth.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("taxonomy_entity_index_tid_depth")
 */
class TaxonomyEntityIndexTidDepth extends IndexTidDepth {

  /**
   * Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $termStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $termStorage);

    $this->termStorage = $termStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $this->ensureMyTable();

    if (!empty($this->options['break_phrase'])) {
      $break = static::breakString($this->argument);
      if ($break->value === [-1]) {
        return FALSE;
      }

      $operator = (count($break->value) > 1) ? 'IN' : '=';
      $tids = $break->value;
    }
    else {
      $operator = "=";
      $tids = $this->argument;
    }
    // Now build the subqueries.
    $subquery = db_select('taxonomy_entity_index', 'tn');
    $subquery->addField('tn', 'entity_id');
    $where = db_or()->condition('tn.tid', $tids, $operator);
    $last = "tn";

    if ($this->options['depth'] > 0) {
      $subquery->leftJoin('taxonomy_term__parent', 'th', "th.entity_id = tn.tid");
      $last = "th";
      foreach (range(1, abs($this->options['depth'])) as $count) {
        $subquery->leftJoin('taxonomy_term__parent', "th$count", "$last.parent_target_id = th$count.entity_id");
        $where->condition("th$count.entity_id", $tids, $operator);
        $last = "th$count";
      }
    }
    elseif ($this->options['depth'] < 0) {
      foreach (range(1, abs($this->options['depth'])) as $count) {
        $subquery->leftJoin('taxonomy_term__parent', "th$count", "$last.entity_id = th$count.parent_target_id");
        $where->condition("th$count.entity_id", $tids, $operator);
        $last = "th$count";
      }
    }

    $subquery->condition($where);
    $this->query->addWhere(0, "$this->tableAlias.$this->realField", $subquery, 'IN');
  }

}
