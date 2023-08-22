/**
 * @file
 */

(($, Drupal, once) => {
  Drupal.behaviors.vaGovMediaLibraryReusableSaveAndSelect = {
    attach: () => {
      $(document).ajaxComplete(() => {
        $("input.field_media_in_library[type=checkbox]:not(:checked)").each(
          () => {
            $(".ui-dialog-buttonpane button").first().hide();
          }
        );
        $(
          once(
            "vaGovMediaLibraryReusableSaveAndSelect",
            "input.field_media_in_library",
            context
          )
        ).change((object) => {
          if (object.checked) {
            $(".ui-dialog-buttonpane button").first().show();
          } else {
            $(".ui-dialog-buttonpane button").first().hide();
          }
        });
      });
    },
  };
})(jQuery, window.Drupal, window.once);
