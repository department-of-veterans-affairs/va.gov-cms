import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`my workbench access sections are set to {string}`, (value) =>
  cy.setWorkbenchAccessSections(value)
);
