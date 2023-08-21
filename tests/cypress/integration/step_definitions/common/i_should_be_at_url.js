import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I should be at {string}`, (url) => cy.url().should("include", url));
