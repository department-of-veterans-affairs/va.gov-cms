/**
 * @file
 * Magichead behaviors.
 */
((Drupal, $) => {

  /**
   *
   */
  const showColumnsForErrors = (tableId) => {
    const $table = $(once.filter('tabledrag', `table#${tableId}`));
    // Show weight/parent cells and headers.
    $table.find('.tabledrag-hide').css('display', '');
    // Hide TableDrag handles.
    $table.find('.tabledrag-handle').css('display', 'none');
    // Increase the colspan for any columns where it was previously reduced.
    $table.find('.tabledrag-has-colspan').each(function () {
      this.colSpan += 1;
    });
  };

  Drupal.behaviors.vaGovMagicheadShowErrors = {
    attach() {
      const magicheadTables =
        document.querySelectorAll(
          "div.field--type-magichead table.field-multiple-table.draggable-table"
        ) || [];
      const errors = {};
      magicheadTables.forEach((element) => {
        const $element = $(element);
        const tableId = element.id;
        const hasError = $element.find("td .form-item--error");
        if (hasError && !(tableId in errors)) {
          errors[tableId] = tableId;
        }
      });
      Object.values(errors).forEach((value) => showColumnsForErrors(value));
    },
  };
})(Drupal, jQuery);
