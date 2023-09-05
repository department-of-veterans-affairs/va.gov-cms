import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I add a main content block with a link to a {string} file`, (type) => {
  cy.addMainContentBlockWithFile(type);
});

Then(`I add a main content block with rich text {string}`, (text) => {
  cy.addMainContentBlockWithRichText(text);
});
