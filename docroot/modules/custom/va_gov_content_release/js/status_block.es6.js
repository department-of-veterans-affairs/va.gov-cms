/**
 * @file
 */

(($, Drupal) => {
  function refreshStatusBlock(url) {
    $.get(url, (data) => {
      $(".content-release-status-block").html(data);
    });
  }

  Drupal.behaviors.contentRelease = Drupal.behaviors.contentRelease || {};

  Drupal.behaviors.contentRelease.statusBlock = {
    attach(context, settings) {
      $(window, context)
        .once("contentRelease.statusBlock")
        .on("load", () => {
          window.setInterval(() => {
            refreshStatusBlock(
              settings.contentRelease.statusBlock.blockRefreshPath
            );
          }, 10000);
        });
    },
  };
})(jQuery, window.Drupal);
