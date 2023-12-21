/**
 * Attach a behavior to filter out unwanted schemas for OpenAPI UI viewing.
 */
document.addEventListener('DOMContentLoaded', function () {
  // Get all the rows in the table body.
  const rows = document.querySelectorAll('#block-vagovclaro-content table tbody tr');

  rows.forEach(function (row) {
    // Get the first cell (td) of the row, which contains the schema label.
    const schemaLabel = row.cells[0];

    // Remove all but 'VA.gov JSON:API'.
    if (schemaLabel.textContent.trim() !== 'VA.gov JSON:API') {
      row.remove();
    }
  });
});

