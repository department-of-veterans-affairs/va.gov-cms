import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`my workbench access sections are not set`, () => cy.unsetWorkbenchAccessSections());
