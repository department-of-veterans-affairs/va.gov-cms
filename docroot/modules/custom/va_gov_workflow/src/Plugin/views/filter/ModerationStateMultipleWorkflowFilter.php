<?php

namespace Drupal\va_gov_workflow\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Drupal\workflows\Entity\Workflow;

/**
 * Filters by moderation state across multiple workflows.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("moderation_state_multiple_workflow_filter")
 */
class ModerationStateMultipleWorkflowFilter extends ManyToOne {

  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = $this->t('Filter by workflow state');
    $this->definition['options callback'] = [$this, 'generateOptions'];
    $this->currentDisplay = $view->current_display;
  }

  /**
   * Helper function that generates the options.
   *
   * @return array
   *   An array of states and their ids.
   */
  public function generateOptions() {
    $workflows = Workflow::loadMultipleByType('content_moderation');
    $filter_items = [];
    foreach ($workflows as $workflow) {
      $states = $workflow->getTypePlugin()->getStates();
      foreach ($states as $state_id => $state) {
        // Merge labels if they diverge from the machine name.
        // Otherwise, two states with the same machine name but different label
        // will cause the last one listed to win.
        if (empty($filter_items[$state_id])) {
          // It is the first entry, so add it as is.
          $filter_items[$state_id] = $state->label();
        }
        elseif (!empty($filter_items[$state_id]) && ($filter_items[$state_id] !== $state->label())) {
          // This machine name exists with a different label, so combine them.
          $filter_items[$state_id] = "{$filter_items[$state_id]} | {$state->label()}";
        }
      }
    }
    return $filter_items;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $configuration = [
        'table' => 'content_moderation_state_field_data',
        'field' => 'content_entity_revision_id',
        'left_table' => 'node_field_data',
        'left_field' => 'vid',
        'operator' => '=',
      ];
      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->query->addRelationship('content_moderation_state_field_data', $join, 'node_field_data');
      $this->query->addWhere('AND', 'content_moderation_state_field_data.moderation_state', $this->value, '=');
      $this->query->addWhere('AND', 'content_moderation_state_field_data.content_entity_type_id', 'node', '=');
    }
  }

}
