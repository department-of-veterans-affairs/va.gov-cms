import { Then } from "cypress-cucumber-preprocessor/steps";

Then("I hover over {string}", (selector) => {
  cy.get(selector).realHover();
});
