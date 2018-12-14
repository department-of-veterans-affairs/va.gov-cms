(function ($, Drupal) {
  Drupal.behaviors.vaGovAlertForm = {
    attach: function () {
      $(document).ajaxComplete(function() {
        $('.field--name-field-alert-trigger-text').css('display', function () {
          return ('expanding' === $(this).parent().children('.field--type-list-string').find('select').val()) ? 'block' : 'none';
        });
      });
    }
  };
})(jQuery, window.Drupal);
