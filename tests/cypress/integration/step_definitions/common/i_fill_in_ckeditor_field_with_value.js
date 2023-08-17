import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I fill in ckeditor {string} with {string}`, (label, value) => {
  cy.type_ckeditor(label, value);
});
