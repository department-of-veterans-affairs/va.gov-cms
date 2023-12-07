import { Then } from "@badeball/cypress-cucumber-preprocessor";
import { faker } from "@faker-js/faker";

Then(`I fill in ckeditor {string} with {string}`, (label, value) => {
  cy.type_ckeditor(label, value);
});

Then(`I fill in ckeditor {string} with fake text`, (label) => {
  cy.type_ckeditor(label, faker.lorem.sentence());
});

Then(`I fill in ckeditor {string} with fake text including links`, (label) => {
  let text = faker.lorem.sentence();
  const linkHref = faker.internet.url();
  const linkText = faker.lorem.word();
  const link = `<a href="${linkHref}">${linkText}</a>`;
  text += ` ${link}`;
  text += ` ${faker.lorem.sentence()}`;
  cy.type_ckeditor(label, text);
});
