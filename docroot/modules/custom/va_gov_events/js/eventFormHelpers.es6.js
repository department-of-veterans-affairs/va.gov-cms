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

  const toggleCtaLinkRequired = (required = true) => {
    const addRemove = required ? "add" : "remove";
    fieldLinkWrapper.style.display = required ? "block" : "none";
    fieldLinkInput.required = required ? "required" : "";
    fieldLinkWrapperLabel.classList[addRemove](
      "js-form-required",
      "form-required"
    );
  };

  const toggleRegistrationElements = () => {
    const toggleVal = !!includeRegistrationsBool.checked;
    let elementDisplayStyle = "block";
    if (!toggleVal) {
      fieldLinkInput.value = "";
      ctaSelect.value = "_none";
      elementDisplayStyle = "none";
      registrationRequiredBool.checked = false;
      toggleCtaLinkRequired(false);
    }
    targetRegistrationElements.forEach((element) => {
      element.style.display = elementDisplayStyle;
    });
  };

  const requireCTA = () => {
    // Default should be hidden.
    fieldLinkWrapper.style.display = "none";

    // If there is a cta on page load, require an input.
    if (ctaSelect.value !== "_none") {
      toggleCtaLinkRequired();
    }

    // Check on change if cta value, and require input if so.
    ctaSelect.addEventListener("change", (e) => {
      toggleCtaLinkRequired(false);
      if (e.target.value !== "_none") {
        toggleCtaLinkRequired();
      } else {
        fieldLinkInput.value = "";
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
      document.addEventListener("DOMContentLoaded", operate());
    },
  };
})(Drupal);
