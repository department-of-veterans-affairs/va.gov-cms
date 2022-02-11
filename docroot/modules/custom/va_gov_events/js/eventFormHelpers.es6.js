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
      ".centralized.reduced-padding, #edit-field-event-registrationrequired-wrapper, #edit-field-event-cta-wrapper, #edit-group-registration-link, #group-registration-link, #edit-field-additional-information-abo-wrapper"
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

  const requireCTA = () => {
    const ctaSelect = document.getElementById("edit-field-event-cta");
    const fieldLinkWrapper = document.getElementById("edit-field-link-wrapper");
    const fieldLinkInput = document.getElementById("edit-field-link-0-uri");
    const fieldLinkWrapperLabel = document.querySelector(
      "#edit-field-link-wrapper label"
    );
    // Default should be hidden.
    fieldLinkWrapper.style.display = "none";

    // If there is a cta on page load, require an input.
    if (ctaSelect.value !== "_none") {
      fieldLinkWrapper.style.display = "block";
      fieldLinkInput.required = "required";
      fieldLinkWrapperLabel.classList.add("js-form-required", "form-required");
    }

    // Check on change if cta value, and require input if so.
    ctaSelect.addEventListener("change", (e) => {
      fieldLinkInput.required = "";
      fieldLinkWrapper.style.display = "none";
      fieldLinkWrapperLabel.classList.remove(
        "js-form-required",
        "form-required"
      );
      if (e.target.value !== "_none") {
        fieldLinkInput.attributes.required = "required";
        fieldLinkWrapperLabel.classList.add(
          "js-form-required",
          "form-required"
        );
        fieldLinkWrapper.style.display = "block";
      }
    });
  };

  const toggleAddressRequiredFields = (enableDisable, addRemove) => {
    // Address field.
    if (document.getElementById("edit-field-address-0-address-address-line1")) {
      document.getElementById(
        "edit-field-address-0-address-address-line1"
      ).required = enableDisable;
    }
    if (
      document.querySelector(
        "label[for='edit-field-address-0-address-address-line1']"
      )
    ) {
      document
        .querySelector(
          "label[for='edit-field-address-0-address-address-line1']"
        )
        .classList[addRemove]("form-required");
    }
    // City field.
    if (document.getElementById("edit-field-address-0-address-locality")) {
      document.getElementById(
        "edit-field-address-0-address-locality"
      ).required = enableDisable;
    }
    if (
      document.querySelector(
        "label[for='edit-field-address-0-address-locality']"
      )
    ) {
      document
        .querySelector("label[for='edit-field-address-0-address-locality']")
        .classList[addRemove]("form-required");
    }
    // State field.
    if (
      document.getElementById(
        "edit-field-address-0-address-administrative-area"
      )
    ) {
      document.getElementById(
        "edit-field-address-0-address-administrative-area"
      ).required = enableDisable;
    }
    if (
      document.querySelector(
        "label[for='edit-field-address-0-address-administrative-area']"
      )
    ) {
      document
        .querySelector(
          "label[for='edit-field-address-0-address-administrative-area']"
        )
        .classList[addRemove]("form-required");
    }
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
    requireCTA();
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
      document.addEventListener("DOMContentLoaded", operate());
    },
  };
})(Drupal);
