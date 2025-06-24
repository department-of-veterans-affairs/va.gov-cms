import { Given } from "@badeball/cypress-cucumber-preprocessor";

/**
 * Helper function to fetch and validate a page of events from the JSON:API.
 * @param {URLSearchParams} params - The query parameters for the request.
 */
function fetchAndCheckEventPage(params) {
  cy.request(`/jsonapi/node/event?${params.toString()}`).then((response) => {
    expect(response.status).to.eq(200);
  });
}

Given(
  "I can successfully follow the event listing API flow for {string}",
  (path) => {
    // Request the router to translate the path to a UUID.
    cy.request(`/router/translate-path?path=${path}`).then((response) => {
      expect(response.status).to.eq(200);
      const { uuid } = response.body.entity;
      // eslint-disable-next-line no-unused-expressions
      expect(uuid, `Expected to find a UUID for path: ${path}`).to.not.be
        .undefined;

      // Use the UUID to fetch the event_listing node.
      cy.request(
        `/jsonapi/node/event_listing/${uuid}?include=field_office`
      ).then((eventListingResponse) => {
        expect(eventListingResponse.status).to.eq(200);
        expect(eventListingResponse.body.data.id).to.eq(uuid);

        // Use the UUID to fetch related events.
        const eventFilterParams = new URLSearchParams({
          "filter[outreach_cal_group][group][conjunction]": "OR",
          "filter[field_listing.id][condition][path]": "field_listing.id",
          "filter[field_listing.id][condition][value]": uuid,
          "filter[field_listing.id][condition][memberOf]": "outreach_cal_group",
          include: "field_listing,field_administration,field_facility_location",
          "page[limit]": 50,
          "page[offset]": 0,
          sort: "-created",
        });

        // Fetch the first page of events.
        fetchAndCheckEventPage(eventFilterParams);

        // Fetch the second page of events.
        eventFilterParams.set("page[offset]", 50);
        fetchAndCheckEventPage(eventFilterParams);
      });
    });
  }
);
