/**
 * @file
 */

((Drupal) => {
  const alterHelpTextLocationOnDateField = (items) => {
    if (items.length > 0) {
      items.forEach((item) => {
        if (
          item.previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling.nodeName === "H4"
        ) {
          item.previousElementSibling.previousElementSibling.after(item);
          item.style.margin = "0 0 10px 0px";
        }
      });
    }
  };

  const alterHelpTextLocationOnTextField = (items) => {
    if (items.length > 0) {
      items.forEach((item) => {
        if (
          item.previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling.nodeName ===
            "LABEL"
        ) {
          item.previousElementSibling.previousElementSibling.after(item);
          item.style.margin = "0 0 10px 0px";
        }
      });
    }
  };

  const alterHelpTextLocationOnLongTextPlainField = (items) => {
    if (items.length > 0) {
      items.forEach((item) => {
        if (
          item.previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling
            .previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling
            .previousElementSibling.nodeName === "LABEL"
        ) {
          item.previousElementSibling.previousElementSibling.previousElementSibling.after(
            item
          );
          item.style.margin = "0 0 10px 0px";
        }
      });
    }
  };

  const alterHelpTextLocationOnLongTextFullHtmlField = (items) => {
    if (items.length > 0) {
      items.forEach((item) => {
        if (
          item.previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling !== null &&
          item.previousElementSibling.previousElementSibling
            .firstElementChild !== null &&
          item.previousElementSibling.previousElementSibling.firstElementChild
            .nodeName === "LABEL"
        ) {
          item.previousElementSibling.previousElementSibling.firstElementChild.after(
            item
          );
          item.style.margin = "0 0 10px 0px";
        }
      });
    }
  };

  const alterHelpTextLocationOnParagraphsExperimentalWidget = (items) => {
    if (items.length > 0) {
      items.forEach((item) => {
        if (
          item.previousElementSibling !== null &&
          item.previousElementSibling.firstElementChild !== null &&
          item.previousElementSibling.firstElementChild.firstElementChild !==
            null &&
          item.previousElementSibling.firstElementChild.firstElementChild
            .firstElementChild.firstElementChild !== null &&
          item.previousElementSibling.firstElementChild.firstElementChild
            .firstElementChild.firstElementChild.nodeName === "H4"
        ) {
          item.style.fontWeight = "400";
          item.style.textTransform = "none";
          item.style.margin = "0 0 10px 0px";
          item.previousElementSibling.firstElementChild.firstElementChild.firstElementChild.firstElementChild.after(
            item
          );
        }
      });
    }
  };

  /**
   * Moves help text from below field / widget to below label.
   * This approach relies on html structure patterns to broadly
   * target fields and widgets. It is field name / type agnostic.
   * The patterns below have comments that indicate their general scope.
   */
  Drupal.behaviors.vaGovtextContentDescriptionPlacement = {
    attach(context) {
      // Get help texts.
      const textareaDescriptions = context.querySelectorAll(".description");
      alterHelpTextLocationOnDateField(textareaDescriptions);
      alterHelpTextLocationOnTextField(textareaDescriptions);
      alterHelpTextLocationOnLongTextPlainField(textareaDescriptions);
      alterHelpTextLocationOnLongTextFullHtmlField(textareaDescriptions);
      alterHelpTextLocationOnParagraphsExperimentalWidget(textareaDescriptions);
    },
  };
})(Drupal);
