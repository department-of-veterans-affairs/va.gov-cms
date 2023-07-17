/**
 * @file
 */

(($, Drupal, once) => {
  function refreshStatusBlock(url) {
    $.get(url, (data) => {
      $(".content-release-status-block").html(data);
    });
  }

  Drupal.behaviors.vaGovContentReleaseStatusBlock = {
    attach: (context, settings) => {
      $(once("contentReleaseStatusBlock", window, context)).on("load", () => {
        window.setInterval(() => {
          refreshStatusBlock(
            settings.contentReleaseStatusBlock.blockRefreshPath
          );
        }, 10000);
      });
    },
  };
})(jQuery, Drupal, once);
