/**
 * @file
 */

(($, Drupal) => {
  function refreshStatusBlock(url) {
    $.get(url, (data) => {
      $("#edit-content-release-status-block--2").html(data);
    });
  }

  Drupal.behaviors.contentReleaseStatusBlock = {
    attach(context, settings) {
      $(window, context)
        .once("contentReleaseStatusBlock")
        .on("load", () => {
          window.setInterval(() => {
            refreshStatusBlock(
              settings.contentReleaseStatusBlock.blockRefreshPath
            );
          }, 10000);
        });
    },
  };
})(jQuery, window.Drupal);
