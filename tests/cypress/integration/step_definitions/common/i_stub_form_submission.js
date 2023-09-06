/* eslint-disable max-nested-callbacks */
import { Given } from "@badeball/cypress-cucumber-preprocessor";
// eslint-disable-next-line import/no-extraneous-dependencies
import qs from "qs";

Given("I stub form submission for the current page", () => {
  cy.location("pathname").then((pathname) => {
    cy.intercept(
      {
        pathname,
        method: "POST",
        middleware: true,
      },
      (req) => {
        if (typeof req.body === "string" || req.body instanceof String) {
          const body = qs.parse(req.body);
          body.is_under_test = "true";
          req.body = qs.stringify(body);
        } else {
          req.body.is_under_test = "true";
        }
        req.continue();
      }
    ).as("formSubmission");
  });
});
