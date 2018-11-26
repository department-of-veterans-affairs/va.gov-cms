<?php
/**
 * @file
 * Preprocess function for blocks.
 */

/**
 * Implements hook_preprocess_paragraph().
 */
function vagov_preprocess_paragraph(&$variables) {

  // Always set the USWDS-specific "uswds_grid_class" variable.
  if (!isset($variables['uswds_grid_class'])) {
    $variables['uswds_grid_class'] = FALSE;
  }

  // All of this is only necessary if edge-to-edge mode is on.
  if (!_uswds_edge_to_edge_mode()) {
    // But we should still set the grid class to usa-grid-full.
    $variables['uswds_grid_class'] = 'usa-grid-full';
    return;
  }

  // We want to make sure that Paragraphs are on the grid.
  // But there are some exceptions. We don't need to add the grid class if...

  // ...if this is a Paragraph on a Paragraph.
  $paragraph = $variables['paragraph'];
  if ('paragraph' == $paragraph->getParentEntity()->getEntityTypeId()) {
    return;
  }

  // ...if this is one of a few Paragraph types that shouldn't be on the grid.
  // These types are provided by the USWDS Paragraphs module.
  $exclude_types = [
    'uswds_hero_callout',
    'uswds_media_block',
  ];
  if (in_array($paragraph->bundle(), $exclude_types)) {
    return;
  }

  // If still here, add the grid class.
  $variables['uswds_grid_class'] = 'usa-grid';

}



/**
 * Implements hook_preprocess_paragraph__collapsible_panel().
 */
function vagov_preprocess_paragraph__collapsible_panel(&$variables) {
  $accordion = $variables['paragraph'];

  if ($accordion->field_collapsible_panel_multi->value) {
    $variables['attributes']['aria-multiselectable'] = 'true';
  }

  if ($accordion->field_collapsible_panel_bordered->value) {
    $variables['attributes']['class'][] = 'usa-accordion-bordered';
  }
  else {
    $variables['attributes']['class'][] = 'usa-accordion';
  }

  if ($accordion->field_collapsible_panel_expand->value) {
    $variables['content']['field_va_paragraphs'][0]['#uswds_expand'] = TRUE;
  }
}

/**
 * Implements hook_preprocess_paragraph__collapsible_panel_item().
 */
function vagov_preprocess_paragraph__collapsible_panel_item(&$variables) {
  $expanded = (!empty($variables['elements']['#uswds_expand']));
  $variables['expanded'] = ($expanded) ? 'true' : 'false';

  $controls = 'va-paragraph-' . $variables['paragraph']->id();
  $variables['controls'] = $controls;
}