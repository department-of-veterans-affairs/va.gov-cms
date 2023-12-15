/**
 * @file
 * Provides Swagger integration.
 */

(function ($, Drupal, drupalSettings) {
  /**
   * Attach a behavior to filter out unwanted schemas for OpenAPI UI viewing.
   */
  Drupal.behaviors.vaGovJsonSchemasTableFilter = {
    attach: function (context, settings) {
      // Get all the rows in the table body
      var rows = document.querySelectorAll('#block-vagovclaro-content table tbody tr');

      rows.forEach(function(row) {
        // Get the first cell (td) of the row
        var firstCell = row.cells[0];

        // Check if the text content of the first cell is not 'VA.gov JSON:API'
        if (firstCell.textContent.trim() !== 'VA.gov JSON:API') {
          // Hide the row or remove it
          // row.style.display = 'none'; // Hides the row
          row.remove(); // Removes the row completely
        }
      });
    }
  }
})(jQuery, Drupal, drupalSettings);
