import { Given } from "cypress-cucumber-preprocessor/steps";


Given(`the path should contain {string}`, (string) => {
  cy.window().then((window) => {
    const pagePath = window.location.pathname;
    expect(pagePath).to('contain', string);
  });
});

Given(`the path should equal {string}`, (string) => {
  cy.window().then((window) => {
    const pagePath = window.location.pathname;
    expect(pagePath).to('equal', string);
  });
});
