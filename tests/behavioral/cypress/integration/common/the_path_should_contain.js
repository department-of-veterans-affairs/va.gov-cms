import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the path should contain {string}`, (string) => {
  cy.window().then((window) => {
    const pagePath = window.location.pathname;
    expect(pagePath).to('contain', string);
  });
});

Then(`the path should equal {string}`, (string) => {
  cy.window().then((window) => {
    const pagePath = window.location.pathname;
    expect(pagePath).to('equal', string);
  });
});
