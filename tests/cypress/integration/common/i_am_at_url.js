import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I am at {string}`, (url) => cy.visit(url));
