(($, Drupal, once) => {
  const handleTestClick = () => {};

  Drupal.behaviors.formBuilderParagraphsortAndDelete = {
    attach(context) {
      // const config = settings.paragraphsort || {};

      $(
        once(
          "paragraph-sort-and-delete-test",
          ".form-builder-paragraph-sort-and-delete__test",
          context
        )
      ).on("click", handleTestClick);
    },
  };
})(jQuery, Drupal, window.once);
