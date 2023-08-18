/**
 * @file
 */

(($, Drupal, once) => {
  function refreshStatusBlock(url) {
    $.get(url, (data) => {
      $(".content-release-status-block").html(data);
    });
  }

  Drupal.behaviors.contentReleaseStatusBlock = {
    attach: (context, settings) => {
      $(once("contentReleaseStatusBlock", "body", context)).on("load", () => {
        window.setInterval(() => {
          refreshStatusBlock(
            settings.contentReleaseStatusBlock.blockRefreshPath
          );
        }, 10000);
      });
    },
  };
})(jQuery, window.Drupal, window.once);
