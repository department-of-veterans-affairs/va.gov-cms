/**
 * @file
 */

((Drupal) => {
  const includeRegistrationsBool = document.getElementById(
    "edit-field-include-registration-info-value"
  );

  const includeLocationItemsRadios = document.getElementById(
    "edit-field-location-type"
  );

  const toggleRegistrationElements = () => {
    const targetRegistrationElements = document.querySelectorAll(
      ".centralized.reduced-padding, #edit-field-event-registrationrequired-wrapper, #edit-field-event-cta-wrapper, #edit-field-link-wrapper, #edit-field-additional-information-abo-wrapper"
    );
    const toggleVal = !!includeRegistrationsBool.checked;
    targetRegistrationElements.forEach((element) => {
      if (toggleVal) {
        element.style.display = "block";
      } else {
        element.style.display = "none";
      }
    });
  };

  const toggleAddressRequiredFields = (enableDisable, addRemove) => {
    // Address field.
    document.getElementById(
      "edit-field-address-0-address-address-line1"
    ).required = enableDisable;
    document
      .querySelector("label[for='edit-field-address-0-address-address-line1']")
      .classList[addRemove]("form-required");
    // City field.
    document.getElementById(
      "edit-field-address-0-address-locality"
    ).required = enableDisable;
    document
      .querySelector("label[for='edit-field-address-0-address-locality']")
      .classList[addRemove]("form-required");
    // State field.
    document.getElementById(
      "edit-field-address-0-address-administrative-area"
    ).required = enableDisable;
    document
      .querySelector(
        "label[for='edit-field-address-0-address-administrative-area']"
      )
      .classList[addRemove]("form-required");
  };

  const toggleLocationElements = () => {
    const targetLocationElements = document.querySelectorAll(
      "#edit-field-facility-location-wrapper, #edit-field-url-of-an-online-event-wrapper, #edit-field-location-humanreadable-wrapper, #edit-field-address-wrapper"
    );
    targetLocationElements.forEach((element) => {
      element.style.display = "none";
    });
    toggleAddressRequiredFields(false, "remove");
    if (document.getElementById("edit-field-location-type-facility").checked) {
      document.getElementById(
        "edit-field-facility-location-wrapper"
      ).style.display = "block";
      document.getElementById(
        "edit-field-location-humanreadable-wrapper"
      ).style.display = "block";
    }
    if (
      document.getElementById("edit-field-location-type-non-facility").checked
    ) {
      document.getElementById(
        "edit-field-location-humanreadable-wrapper"
      ).style.display = "block";
      document.getElementById("edit-field-address-wrapper").style.display =
        "block";
      toggleAddressRequiredFields(true, "add");
    }
    if (document.getElementById("edit-field-location-type-online").checked) {
      document.getElementById(
        "edit-field-url-of-an-online-event-wrapper"
      ).style.display = "block";
    }
  };

  const operate = () => {
    includeRegistrationsBool.addEventListener("click", () => {
      toggleRegistrationElements();
    });
    toggleRegistrationElements();

    includeLocationItemsRadios.addEventListener("change", () => {
      toggleLocationElements();
    });
    toggleLocationElements();
  };

  Drupal.behaviors.vaGovEventFormHelpers = {
    attach() {
      window.addEventListener("DOMContentLoaded", operate(document));
    },
  };
})(Drupal);
