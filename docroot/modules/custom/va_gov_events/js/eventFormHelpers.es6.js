/**
 * @file
 */

((Drupal) => {
  const registrationRequiredBool = document.getElementById(
    "edit-field-event-registrationrequired-value"
  );
  const includeRegistrationsBool = document.getElementById(
    "edit-field-include-registration-info-value"
  );
  const ctaSelect = document.getElementById("edit-field-event-cta");
  const fieldLinkWrapper = document.getElementById("edit-field-link-wrapper");
  const fieldLinkInput = document.getElementById("edit-field-link-0-uri");
  const fieldLinkWrapperLabel = document.querySelector(
    "#edit-field-link-wrapper label"
  );
  const fieldCtaEmailInput = document.getElementById(
    "edit-field-cta-email-0-value"
  );
  const fieldCtaEmailWrapper = document.getElementById(
    "edit-field-cta-email-wrapper"
  );
  const fieldCtaEmailWrapperLabel = document.querySelector(
    "#edit-field-cta-email-wrapper label"
  );
  const fieldCtaHowToSignUp = document.getElementById(
    "edit-field-how-to-sign-up"
  );
  const fieldCtaHowToSignUpWrapper = document.getElementById(
    "edit-field-how-to-sign-up-wrapper"
  );
  const fieldCtaHowToSignUpLabel = document.querySelector(
    "#edit-field-how-to-sign-up-wrapper label"
  );
  const fieldLocationTypeFacility = document.getElementById(
    "edit-field-location-type-facility"
  );
  const fieldLocationTypeNonFacility = document.getElementById(
    "edit-field-location-type-non-facility"
  );
  const includeLocationItemsRadios = document.getElementById(
    "edit-field-location-type"
  );
  const fieldLocationHumanreadable = document.getElementById(
    "edit-field-location-humanreadable-0-value"
  );
  const fieldLocationHumanreadableWrapper = document.getElementById(
    "edit-field-location-humanreadable-wrapper"
  );
  const targetLocationElements = document.querySelectorAll(
    "#edit-field-facility-location-wrapper, #edit-field-url-of-an-online-event-wrapper, #edit-field-location-humanreadable-wrapper, #edit-field-address-wrapper"
  );
  const fieldFacilityLocationWrapper = document.getElementById(
    "edit-field-facility-location-wrapper"
  );
  const fieldFacilityLocation = document.getElementById(
    "edit-field-facility-location-0-target-id"
  );
  const fieldLocationTypeOnline = document.getElementById(
    "edit-field-location-type-online"
  );
  const fieldAddressWrapper = document.getElementById(
    "edit-field-address-wrapper"
  );
  const fieldUrlOfOnlineEvent = document.getElementById(
    "edit-field-url-of-an-online-event-0-uri"
  );
  const fieldUrlOfOnlineEventWrapper = document.getElementById(
    "edit-field-url-of-an-online-event-wrapper"
  );
  const fieldAddressLine1 = document.getElementById(
    "edit-field-address-0-address-address-line1"
  );
  const fieldAddressLine1Label = document.querySelector(
    "label[for='edit-field-address-0-address-address-line1']"
  );
  const fieldAddressLine2 = document.getElementById(
    "edit-field-address-0-address-address-line2"
  );
  const fieldAddressLocality = document.getElementById(
    "edit-field-address-0-address-locality"
  );
  const fieldAddressLocalityLabel = document.querySelector(
    "label[for='edit-field-address-0-address-locality']"
  );
  const fieldAddressAdminArea = document.getElementById(
    "edit-field-address-0-address-administrative-area"
  );
  const fieldAddressAdminAreaLabel = document.querySelector(
    "label[for='edit-field-address-0-address-administrative-area']"
  );
  const targetRegistrationElements = document.querySelectorAll(
    ".centralized.reduced-padding, #edit-field-event-registrationrequired-wrapper, #edit-field-event-cta-wrapper, #edit-group-registration-link, #group-registration-link, #edit-field-additional-information-abo-wrapper"
  );

  const toggleCtaInputRequired = (label, wrapper, input, required = true) => {
    const addRemove = required ? "add" : "remove";
    wrapper.style.display = required ? "block" : "none";
    input.required = required ? "required" : "";
    label.classList[addRemove]("js-form-required", "form-required");
  };

  const toggleAllCtaInputsRequired = (required = true) => {
    toggleCtaInputRequired(
      fieldLinkWrapperLabel,
      fieldLinkWrapper,
      fieldLinkInput,
      required
    );
    toggleCtaInputRequired(
      fieldCtaEmailWrapperLabel,
      fieldCtaEmailWrapper,
      fieldCtaEmailInput,
      required
    );
    toggleCtaInputRequired(
      fieldCtaHowToSignUpLabel,
      fieldCtaHowToSignUpWrapper,
      fieldCtaHowToSignUp,
      required
    );
  };

  const emptyAllCtaInputs = () => {
    fieldCtaEmailInput.value = "";
    fieldLinkInput.value = "";
    fieldCtaHowToSignUp.value = "_none";
  };

  const toggleRegistrationElements = () => {
    const toggleVal = !!includeRegistrationsBool.checked;
    let elementDisplayStyle = "block";
    if (!toggleVal) {
      fieldLinkInput.value = "";
      fieldCtaEmailInput.value = "";
      ctaSelect.value = "_none";
      elementDisplayStyle = "none";
      registrationRequiredBool.checked = false;
      toggleAllCtaInputsRequired(false);
    }
    targetRegistrationElements.forEach((element) => {
      element.style.display = elementDisplayStyle;
    });
  };

  const requireCTA = () => {
    // Default should be hidden.
    fieldLinkWrapper.style.display = "none";
    fieldCtaEmailWrapper.style.display = "none";
    fieldCtaHowToSignUpWrapper.style.display = "none";

    // If there is a cta on page load, show conditional fields.
    if (ctaSelect.value !== "_none") {
      // If there is an email field value, display the email field.
      if (fieldCtaEmailInput.value.length > 0) {
        toggleCtaInputRequired(
          fieldCtaEmailWrapperLabel,
          fieldCtaEmailWrapper,
          fieldCtaEmailInput,
          true
        );
        // Also display the 'how to apply' field, and set the value accordingly.
        toggleCtaInputRequired(
          fieldCtaHowToSignUpLabel,
          fieldCtaHowToSignUpWrapper,
          fieldCtaHowToSignUp,
          true
        );
        fieldCtaHowToSignUp.value = "email";
      }
      // If there is an url field value, display the url field.
      else if (fieldLinkInput.value.length > 0) {
        toggleCtaInputRequired(
          fieldLinkWrapperLabel,
          fieldLinkWrapper,
          fieldLinkInput,
          true
        );
        // Also display the 'how to apply' field, and set the value accordingly.
        toggleCtaInputRequired(
          fieldCtaHowToSignUpLabel,
          fieldCtaHowToSignUpWrapper,
          fieldCtaHowToSignUp,
          true
        );
        fieldCtaHowToSignUp.value = "url";
      }
      // Otherwise, display the 'how to sign up' field.
      else {
        toggleCtaInputRequired(
          fieldCtaHowToSignUpLabel,
          fieldCtaHowToSignUpWrapper,
          fieldCtaHowToSignUpWrapper
        );
      }
    }

    // Check on change of cta value, and require input if so.
    ctaSelect.addEventListener("change", (e) => {
      emptyAllCtaInputs();
      toggleAllCtaInputsRequired(false);
      if (e.target.value !== "_none") {
        toggleCtaInputRequired(
          fieldCtaHowToSignUpLabel,
          fieldCtaHowToSignUpWrapper,
          fieldCtaHowToSignUp
        );
      }
    });

    fieldCtaHowToSignUp.addEventListener("change", (e) => {
      switch (e.target.value) {
        case "url":
          // Empty email field.
          fieldCtaEmailInput.value = "";
          // Hide email field.
          toggleCtaInputRequired(
            fieldCtaEmailWrapperLabel,
            fieldCtaEmailWrapper,
            fieldCtaEmailInput,
            false
          );
          // Display url field and make required.
          toggleCtaInputRequired(
            fieldLinkWrapperLabel,
            fieldLinkWrapper,
            fieldLinkInput
          );
          break;

        case "email":
          // Empty url field.
          fieldLinkInput.value = "";
          // Hide url field.
          toggleCtaInputRequired(
            fieldLinkWrapperLabel,
            fieldLinkWrapper,
            fieldLinkInput,
            false
          );
          // Display email field and make required.
          toggleCtaInputRequired(
            fieldCtaEmailWrapperLabel,
            fieldCtaEmailWrapper,
            fieldCtaEmailInput
          );
          break;

        default:
          // Hide both url and email fields and empty their values.
          toggleCtaInputRequired(
            fieldLinkWrapperLabel,
            fieldLinkWrapper,
            fieldLinkInput,
            false
          );
          toggleCtaInputRequired(
            fieldCtaEmailWrapperLabel,
            fieldCtaEmailWrapper,
            fieldCtaEmailInput,
            false
          );
          fieldLinkInput.value = "";
          fieldCtaEmailInput.value = "";
          break;
      }
    });
  };

  const toggleAddressRequiredFields = (enableDisable, addRemove) => {
    // Address field.
    if (fieldAddressLine1) {
      fieldAddressLine1.required = enableDisable;
    }
    if (fieldAddressLine1Label) {
      fieldAddressLine1Label.classList[addRemove]("form-required");
    }
    // City field.
    if (fieldAddressLocality) {
      fieldAddressLocality.required = enableDisable;
    }
    if (fieldAddressLocalityLabel) {
      fieldAddressLocalityLabel.classList[addRemove]("form-required");
    }
    // State field.
    if (fieldAddressAdminArea) {
      fieldAddressAdminArea.required = enableDisable;
    }
    if (fieldAddressAdminAreaLabel) {
      fieldAddressAdminAreaLabel.classList[addRemove]("form-required");
    }
  };

  const toggleLocationElements = () => {
    targetLocationElements.forEach((element) => {
      element.style.display = "none";
    });
    toggleAddressRequiredFields(false, "remove");
    if (fieldLocationTypeFacility.checked) {
      fieldFacilityLocationWrapper.style.display = "block";
      fieldLocationHumanreadableWrapper.style.display = "block";
      fieldUrlOfOnlineEvent.value = "";
      fieldAddressLine1.value = "";
      fieldAddressLine2.value = "";
      fieldAddressLocality.value = "";
      fieldAddressAdminArea.value = "";
    }
    if (fieldLocationTypeNonFacility.checked) {
      fieldLocationHumanreadableWrapper.style.display = "block";
      fieldAddressWrapper.style.display = "block";
      toggleAddressRequiredFields(true, "add");
      fieldUrlOfOnlineEvent.value = "";
      fieldFacilityLocation.value = "";
    }
    if (fieldLocationTypeOnline.checked) {
      fieldUrlOfOnlineEventWrapper.style.display = "block";
      fieldAddressLine1.value = "";
      fieldAddressLine2.value = "";
      fieldAddressLocality.value = "";
      fieldAddressAdminArea.value = "";
      fieldFacilityLocation.value = "";
      fieldLocationHumanreadable.value = "";
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
      operate();
    },
  };
})(Drupal);
