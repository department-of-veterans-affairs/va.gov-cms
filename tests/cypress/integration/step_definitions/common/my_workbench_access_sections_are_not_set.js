import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`my workbench access sections are not set`, () =>
  cy.unsetWorkbenchAccessSections()
);
