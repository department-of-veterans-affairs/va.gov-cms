import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I request {string}`, (url) => cy.request(url));
