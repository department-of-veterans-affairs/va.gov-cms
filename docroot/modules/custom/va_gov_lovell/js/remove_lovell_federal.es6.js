/**
 * @file
 */

((Drupal) => {
  // Grab our field and options.
  const adminFieldOptions = document.querySelectorAll(
    "#edit-field-administration option"
  );
  const vamcSystemFieldOptions = document.querySelectorAll(
    "#edit-field-region-page option"
  );

  // Seek and hide element based on a string match.
  function seekHide(domElement, match) {
    domElement.forEach((i) => {
      if (i.text.includes(match)) {
        i.classList.add("hidden-option");
      }
    });
  }

  const lovellRemover = () => {
    const adminMatcher = "Lovell Federal health care";
    if (adminFieldOptions) {
      seekHide(vamcSystemFieldOptions, adminMatcher);
    }
    if (vamcSystemFieldOptions) {
      seekHide(adminFieldOptions, adminMatcher);
    }
  };

  Drupal.behaviors.vaGovRemoveLovellFederal = {
    attach() {
      lovellRemover();
    },
  };
})(Drupal);
