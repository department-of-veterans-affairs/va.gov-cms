import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`the option {string} from dropdown with selector {string} should be selected`, (option, selector) => {
  cy.get(`${selector} option:selected`).should('have.text', option);
});
