import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then("I hover over {string}", (selector) => {
  cy.get(selector).realHover();
});
