import { Given } from "@badeball/cypress-cucumber-preprocessor";

/**
 * Helper function to fetch and validate related vba_facility_service nodes from the JSON:API.
 * @param {URLSearchParams} params - The query parameters for the request.
 */
function fetchServices(params) {
  cy.request(`/jsonapi/node/vba_facility_service?${params.toString()}`).then(
    (response) => {
      expect(response.status).to.eq(200);
    }
  );
}

Given(
  "I can successfully follow the vba_facility API flow for {string}",
  (path) => {
    // Request the router to translate the path to a UUID.
    cy.request(`/router/translate-path?path=${path}`).then((response) => {
      expect(response.status).to.eq(200);
      const { uuid } = response.body.entity;
      // eslint-disable-next-line no-unused-expressions
      expect(uuid, `Expected to find a UUID for path: ${path}`).to.not.be
        .undefined;

      // Use the UUID to fetch the vab_facility node.
      cy.request(
        `/jsonapi/node/vba_facility/${uuid}?include=field_office`
      ).then((listingResponse) => {
        expect(listingResponse.status).to.eq(200);
        expect(listingResponse.body.data.id).to.eq(uuid);
        // Build params for vba_facility_service(s) fetch
        const params = new URLSearchParams({
          "filter[field_office.id]": uuid,
          include:
            "field_office,field_service_name_and_descripti,field_service_location,field_service_location.field_service_location_address,field_service_location.field_other_phone_numbers,field_service_location.field_phone,field_service_location.field_email_contacts",
          "page[limit]": 50,
          "page[offset]": 0,
        });
        // fetch vba_facility_service(s)
        fetchServices(params);
      });
    });
  }
);
