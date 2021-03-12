/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovMediaLibraryReusableSaveAndSelect = {
    attach: () => {
      $(document).ajaxComplete(() => {
        $("input.field_media_in_library[type=checkbox]:not(:checked)").each(
          () => {
            $(".ui-dialog-buttonpane button").first().hide();
          }
        );
        $("input.field_media_in_library")
          .once()
          .change((object) => {
            if (object.checked) {
              $(".ui-dialog-buttonpane button").first().show();
            } else {
              $(".ui-dialog-buttonpane button").first().hide();
            }
          });
      });
    },
  };
})(jQuery, window.Drupal);
