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

      // Adds a password reset help email to forgot password form.
      $('form#user-pass input#edit-submit').click(function () {
        let val = '[PLEASE ENTER YOUR USERNAME HERE]';
        if ($('form#user-pass #edit-name').val()) {
          val = $('form#user-pass #edit-name').val();
        }
        window.open('mailto:cms-support@va.gov?cc=elijah.lynn@agile6.com&subject=Password Reset Request for ' + val + '&body=Hi support team, Please reset the password for my username: ' + val + '. Thanks!');

        return false;
      })
    }
  };

})(jQuery, window.Drupal);
