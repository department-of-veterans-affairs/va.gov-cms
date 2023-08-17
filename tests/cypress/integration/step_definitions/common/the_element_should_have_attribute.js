import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(
  "the element with selector {string} should have attribute {string}",
  (element, attribute) => {
    cy.get(element).should("have.attr", attribute);
  }
);

Then(
  "the element with selector {string} should not have attribute {string}",
  (element, attribute) => {
    cy.get(element).should("not.have.attr", attribute);
  }
);

Then(
  "the element with selector {string} should have attribute {string} with value {string}",
  (element, attribute, value) => {
    cy.get(element).should("have.attr", attribute, value);
  }
);

Then(
  "the element with selector {string} should have attribute {string} containing value {string}",
  (element, attribute, value) => {
    cy.get(element).should("have.attr", attribute).and("contain", value);
  }
);

Then(
  "the element with selector {string} should have attribute {string} matching expression {string}",
  (element, attribute, regex) => {
    cy.get(element)
      .should("have.attr", attribute)
      .and("match", new RegExp(regex));
  }
);
