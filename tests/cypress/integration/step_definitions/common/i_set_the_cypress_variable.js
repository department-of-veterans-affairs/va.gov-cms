import { When } from "@badeball/cypress-cucumber-preprocessor";
import { faker } from "@faker-js/faker";

When(
  `I set the Cypress variable {string} to {string}`,
  (variableName, value) => {
    cy.wrap(value).as(variableName);
  }
);

When(`I set the Cypress variable {string} to fake text`, (variableName) => {
  cy.wrap(faker.lorem.sentence()).as(variableName);
});

When(
  `I set the Cypress variable {string} to the value of the attribute {string} of the element with xpath {string}`,
  (variableName, attributeName, xpath) => {
    cy.xpath(xpath).then((element) => {
      cy.wrap(element).invoke("attr", attributeName).as(variableName);
    });
  }
);
