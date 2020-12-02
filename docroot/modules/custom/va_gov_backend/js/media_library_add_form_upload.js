/**
 * @file
 */

(function ($, Drupal) {

  Drupal.behaviors.vaGovMediaLibraryReusable = {
    attach: function () {
      $(document).ajaxComplete(function (event, xhr, settings) {
        $('input.field_media_in_library[type=checkbox]:not(checked)').each(function () {
          $('.ui-dialog-buttonpane button').first().hide();
        });
        $('input.field_media_in_library[type=checkbox]:checked').each(function () {
          $('.ui-dialog-buttonpane button').first().show();
        });
        $('input.field_media_in_library')
          .off('.vagov_media_library')
          .on('change.vagov_media_library', function (object) {
            if (this.checked) {
              $('.ui-dialog-buttonpane button').first().show();
            }
            else {
              $('.ui-dialog-buttonpane button').first().hide();
            }
          });
      });
    }
  };

})(jQuery, window.Drupal);
