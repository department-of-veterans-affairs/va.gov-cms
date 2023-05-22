import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I request {string}`, (url) => cy.request(url));
