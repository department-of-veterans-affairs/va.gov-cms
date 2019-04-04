(function ($, Drupal) {

  Drupal.behaviors.vaGovMovePreview = {
    attach: function () {
      // Moves top preview button node forms to top of sidebar.
      $('#edit-top-preview').css({
        'display': 'block',
        'float': 'right',
        'margin-top': '10px',
        'margin-right': '10px',
      }).insertBefore('.entity-meta__header');

    }
  };

  Drupal.behaviors.vaGovRefreshCloseModal = {
    attach: function () {
      // We only do this on node form pages.
      let href = new URL(window.location);
      let path = href.pathname;
      let splitHref = path.split('/');
      if (splitHref.includes('add') || splitHref.includes('edit')) {
        let link = $('.ui-widget-content .ui-dialog-title').text();
        let url = 'http://' + link.replace('You are previewing ', '');
        // Reload node edit page.
        $('.ui-dialog-titlebar .ui-dialog-titlebar-close').click(function () {
          $(location).attr('href', url);
        });
      }
    }
  };

})(jQuery, window.Drupal);
