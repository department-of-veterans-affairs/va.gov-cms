/**
 * @file
 */

(($, Drupal, Tippy) => {
  Drupal.behaviors.vaGovToolbar = {
    attach(context) {
      const loadingText =
        "<div style='height: 20px; width: 40px;' class='claro-spinner'></div>";
      $(document, context)
        .once("content-release-status-icon")
        .each(() => {
          Tippy("#content-release-status-icon", {
            content(reference) {
              reference.removeAttribute("title");
              return loadingText;
            },
            flipOnUpdate: true,
            onCreate(instance) {
              instance._isFetching = false;
              instance._src = null;
              instance._error = null;
            },
            onHidden(instance) {
              instance.setContent(loadingText);
            },
            onShow(instance) {
              if (instance._isFetching || instance._src || instance._error) {
                return;
              }

              instance._isFetching = true;

              $.get("/admin/content/deploy/status", (data) => {
                instance.setContent(data);
              })
                .fail((jqXHR, textStatus, errorThrown) => {
                  instance.setContent(`Request failed. ${errorThrown}`);
                })
                .always(() => {
                  instance._isFetching = false;
                });
            },
            theme: "tippy_popover tippy_popover_center",
            allowHTML: true,
          });
        });
    },
  };
})(jQuery, window.Drupal, window.tippy);
