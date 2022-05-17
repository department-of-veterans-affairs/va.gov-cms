/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovMediaSetOwner = {
    attach: () => {
      const section = document.getElementById("edit-field-administration");
      const owner = document.getElementById(
        "edit-field-media-0-inline-entity-form-field-owner"
      );
      // on page load
      if (owner.value) {
        // check for owner
        owner.value = section.value; // set value of owner equal to section
        section.addEventListener("change", function handleChange() {
          // on section blur/change
          owner.value = section.value; // set value of owner equal to section
        });
      }
    },
  };
})(jQuery, window.Drupal);
