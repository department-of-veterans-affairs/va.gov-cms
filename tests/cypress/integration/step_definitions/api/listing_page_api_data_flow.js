import { Given } from "@badeball/cypress-cucumber-preprocessor";

/**
 * Helper function to fetch and validate a page of events from the JSON:API.
 * @param {URLSearchParams} params - The query parameters for the request.
 * @param {string} singleType - The content type to fetch (e.g., 'event', 'news_story').
 */
function fetchAndCheckListingPage(params, singleType) {
  cy.request(`/jsonapi/node/${singleType}?${params.toString()}`).then(
    (response) => {
      expect(response.status).to.eq(200);
    }
  );
}

Given(
  "I can successfully follow the {string} API flow for {string}",
  (type, path) => {
    // Request the router to translate the path to a UUID.
    cy.request(`/router/translate-path?path=${path}`).then((response) => {
      expect(response.status).to.eq(200);
      const { uuid } = response.body.entity;
      // eslint-disable-next-line no-unused-expressions
      expect(uuid, `Expected to find a UUID for path: ${path}`).to.not.be
        .undefined;

      // Use the UUID to fetch the event_listing node.
      cy.request(`/jsonapi/node/${type}/${uuid}?include=field_office`).then(
        (listingResponse) => {
          expect(listingResponse.status).to.eq(200);
          expect(listingResponse.body.data.id).to.eq(uuid);
          const pageLength = {
            event_listing: 50,
            story_listing: 10,
          };
          // Use the UUID to fetch related events.
          const filterParams = {
            event_listing: new URLSearchParams({
              "filter[outreach_cal_group][group][conjunction]": "OR",
              "filter[field_listing.id][condition][path]": "field_listing.id",
              "filter[field_listing.id][condition][value]": uuid,
              "filter[field_listing.id][condition][memberOf]":
                "outreach_cal_group",
              include:
                "field_listing,field_administration,field_facility_location",
              "page[limit]": pageLength.event_listing,
              "page[offset]": 0,
              sort: "-created",
            }),
            story_listing: new URLSearchParams({
              "filter[field_listing.id][condition][path]": "field_listing.id",
              include: "field_media,field_media.image,field_listing",
              "page[limit]": pageLength.story_listing,
              "page[offset]": 0,
              sort: "-created",
            }),
          };
          const singleTypes = {
            event_listing: "event",
            story_listing: "news_story",
          };
          // Fetch the first page of events.
          fetchAndCheckListingPage(filterParams[type], singleTypes[type]);

          // Fetch the second page of events.
          filterParams[type].set("page[offset]", pageLength[type]);
          fetchAndCheckListingPage(filterParams[type], singleTypes[type]);
        }
      );
    });
  }
);
