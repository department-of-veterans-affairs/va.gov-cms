/**
 * @file
 */

(function ($, Drupal) {
  Drupal.behaviors.vaGovAlertForm = {
    attach: function () {
      $(document).ajaxComplete(function () {
        $('.field--name-field-alert-trigger-text').css('display', function () {
          return ('expanding' === $(this).parent().children('.field--type-list-string').find('select').val()) ? 'block' : 'none';
        });
      });
    }
  };
  Drupal.behaviors.vaGovEmailHelp = {
    attach: function () {
      // Adds a help email popup to Help menu link in admin toolbar.
      $('.toolbar-icon-help-main').click(function () {
        window.open('mailto:vacmssupport@va.gov?subject=Help desk Support Request&body=Hello Support Team, [Insert your issue here. Please also update the subject line with the specific nature of your request (ie “URL Redirect Request”]');
        return false;
      })
    }
  };

})(jQuery, window.Drupal);
