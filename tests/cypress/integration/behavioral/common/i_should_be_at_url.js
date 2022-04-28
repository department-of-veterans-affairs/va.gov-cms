import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should be at {string}`, (url) => cy.url().should('include', url));
