import { Given } from "@badeball/cypress-cucumber-preprocessor";

/**
 * Helper function to fetch facilities for a given region page.
 * @param {string} regionPageId - The UUID of the region page (health_care_region_page).
 * @return {Cypress.Chainable<string[]>} Array of facility IDs
 */
function fetchFacilities(regionPageId) {
  return cy
    .request(
      `/jsonapi/node/health_care_local_facility?filter[status]=1&filter[field_region_page.id]=${regionPageId}&page[limit]=50&page[offset]=0`
    )
    .then((response) => {
      expect(response.status).to.eq(200);
      return response.body.data.map((facility) => facility.id);
    });
}

/**
 * Helper function to build filter parameters for vha_facility_nonclinical_service.
 * @param {string[]} facilityIds - Array of facility IDs to filter by.
 * @return {URLSearchParams} Query parameters
 */
function buildServiceParams(facilityIds) {
  const params = new URLSearchParams({
    "page[limit]": "1",
    "filter[status]": "1",
    "filter[field_service_name_and_descripti.name]": "Medical records",
    "filter[field_facility_location.status]": "1",
    include:
      "field_facility_location,field_service_location,field_service_location.field_email_contacts,field_service_location.field_other_phone_numbers,field_service_location.field_service_location_address,field_service_location.field_phone",
  });

  // Add facility ID filters using the IN operator format (matching original test structure)
  // First add all the value filters
  facilityIds.forEach((facilityId, index) => {
    params.append(
      `filter[field_facility_location.id][condition][value][${index}]`,
      facilityId
    );
  });
  // Then add the condition path and operator
  params.append(
    "filter[field_facility_location.id][condition][path]",
    "field_facility_location.id"
  );
  params.append(
    "filter[field_facility_location.id][condition][operator]",
    "IN"
  );

  return params;
}

/**
 * Helper function to fetch and validate vha_facility_nonclinical_service nodes.
 * @param {string[]} facilityIds - Array of facility IDs to filter by.
 */
function fetchServices(facilityIds) {
  const params = buildServiceParams(facilityIds);
  cy.request(
    `/jsonapi/node/vha_facility_nonclinical_service?${params.toString()}`
  ).then((response) => {
    expect(response.status).to.eq(200);
  });
}

/**
 * Helper function to fetch facilities and then services.
 * @param {string} regionPageId - The UUID of the region page.
 * @return {Cypress.Chainable<void>} - Returns the Cypress promise
 */
function fetchFacilitiesAndServices(regionPageId) {
  return fetchFacilities(regionPageId).then((facilityIds) => {
    fetchServices(facilityIds);
  });
}

Given(
  "I can successfully follow the vamc_system_medical_records_office API flow for {string}",
  (path) => {
    // Request the router to translate the path to a UUID.
    cy.request(`/router/translate-path?path=${path}`).then((response) => {
      expect(response.status).to.eq(200);
      const { uuid } = response.body.entity;
      // eslint-disable-next-line no-unused-expressions
      expect(uuid, `Expected to find a UUID for path: ${path}`).to.not.be
        .undefined;

      // Process the node and fetch services
      cy.request(
        `/jsonapi/node/vamc_system_medical_records_offi/${uuid}?include=field_office`
      ).then((nodeResponse) => {
        expect(nodeResponse.status).to.eq(200);
        expect(nodeResponse.body.data.id).to.eq(uuid);
        const regionPageId =
          nodeResponse.body.data.relationships.field_office.data.id;
        // eslint-disable-next-line no-unused-expressions
        expect(
          regionPageId,
          `Expected to find a field_office.id for vamc_system_medical_records_offi: ${uuid}`
        ).to.not.be.undefined;

        // Fetch facilities for this region page, then fetch services
        fetchFacilitiesAndServices(regionPageId);
      });
    });
  }
);
