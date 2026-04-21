import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then("I confirm the dialog with the text {string}", (expectedText) => {
  cy.on("window:confirm", (text) => {
    expect(text).to.contain(expectedText);
  });
});
