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
        window.open('mailto:vacmssuport@va.gov?subject=Section%20assignment&amp;body=Dear%20VACMS%20support%20team%2C%0A%5BThis%20is%20a%20template.%20%20You%20can%20delete%20the%20text%20you%20don%E2%80%99t%20need%2C%20and%20feel%20free%20to%20add%20your%20own.%5D%0A%0AI%E2%80%99m%20a%20new%20CMS%20user%2C%20and%20need%20to%20be%20given%20access%20to%20the%20following%20VA.gov%20sections%3A%0A%5BList%20the%20sections%20you%20need%20access%20to%20here.%20If%20you%20aren%E2%80%99t%20sure%2C%20describe%20your%20job%20title%20and%20what%20pages%20you%20need%20to%20work%20on.%5D%0A%0APlease%20assign%20me%20the%20following%20role%3A%20%0A%5BAdd%20which%20role%20you%20need%20here.%5D%0A-%20Content%20editor%3A%20because%20I%20need%20to%20create%2C%20edit%2C%20and%20review%20content%0A-%20Content%20publisher%3A%20because%20I%20also%20need%20to%20publish%20content%0A-%20Content%20admin%3A%20because%20I%20need%20broad%20permissions%2C%20including%20customizing%20URLs%20and%20triggering%20unscheduled%20content%20releases%0A%0AThank%20you.');
        return false;
      })
    }
  };

})(jQuery, window.Drupal);
