import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I view the {string} derivative of the media image`, (derivative) => {
  cy.get("@mediaImageUrl").then((url) => {
    const derivativeUrl = url.replace(
      "/files/",
      `/files/styles/${derivative}/public/`
    );
    cy.wrap(derivativeUrl).as("mediaImageDerivativeUrl");
    cy.request(derivativeUrl);
  });
});
