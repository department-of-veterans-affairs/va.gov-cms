import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I fill in ckeditor {string} with {string}`, (label, value) => {
  cy.type_ckeditor(label, value);
});


