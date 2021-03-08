import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`my workbench access sections are set to {string}`, (value) => cy.setWorkbenchAccessSections(value));
