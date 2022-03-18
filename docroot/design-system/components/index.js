/**
 * Apply the Design System to a single Drupal behavior
 */
import { enableAllComponents } from './design-system';

console.log('drupal behaviors added');

Drupal.behaviors.designSystem = {
  attach($context, settings) {
    // Modify settings here as needed to meet requirements of components
    enableAllComponents($context, settings);
  },
};
