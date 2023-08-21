import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I am at {string}`, (url) => cy.visit(url));
