/**
 * @file
 */

((Drupal) => {
  const fieldTreatments = (dom) => {
    CKEDITOR.config.autoGrow_onStartup.valueOf = 750;
    const adminRoles = ["administrator"];
    // If national content and user isn't admin, start operating.
    if (
      drupalSettings.gtm_data.contentType === "centralized_content" &&
      !adminRoles.some((item) =>
        drupalSettings.gtm_data.userRoles.includes(item)
      )
    ) {
      // Make sure weights aren't toggled on.
      Drupal.tableDrag.prototype.hideColumns(1);

      // Grab our National descriptor paragraphs.
      const ccParagraphs = dom.querySelectorAll(
        "div.cc-special-treatment-paragraph.centralized_content_descriptor"
      );
      // Grab our National wysiwyg paragraphs.
      const wysiParagraphs = dom.querySelectorAll(
        ".draggable.paragraph-type--wysiwyg"
      );
      // Grab our National wysiwyg textareas.
      const wysiParagraphsText = dom.querySelectorAll(
        "div.field--name-field-wysiwyg"
      );

      // Pull out the toggle drag handles on the descriptor paragraphs.
      ccParagraphs.forEach((item) => {
        if (item && item.parentElement) {
          item.parentElement.classList.add("cc-paragraph-header");
          if (item.parentElement.previousElementSibling) {
            item.parentElement.previousElementSibling.classList.add(
              "cc-paragraph-toggle-remove"
            );
          }
        }
      });

      // Add special class to national content wysi paragraphs.
      wysiParagraphs.forEach((item) => {
        if (
          item &&
          item.previousElementSibling &&
          item.previousElementSibling.classList.contains(
            "paragraph-type--centralized-content-descriptor"
          )
        ) {
          item.classList.add("cc-national-wysi-padding");
        }
      });

      // Pull out the toggle drag handles on the wysiwyg paragraphs.
      wysiParagraphsText.forEach((item) => {
        if (
          item &&
          item.parentElement.parentElement.parentElement.parentElement
            .previousElementSibling
        ) {
          item.parentElement.parentElement.parentElement.parentElement.previousElementSibling.classList.add(
            "cc-paragraph-toggle-remove"
          );
        }
      });
    }
  };
  /**
   * Behaviors for manipulationg elements when user shouldn't have access.
   * */
  Drupal.behaviors.nationalDataAccessControl = {
    attach(context) {
      fieldTreatments(context);
    },
  };
})(Drupal);
