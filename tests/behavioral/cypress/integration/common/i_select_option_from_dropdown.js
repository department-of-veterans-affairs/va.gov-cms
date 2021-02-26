import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I select option {string} from dropdown {string}`, (option, label) => {
  cy.findAllByLabelText(label).select(option);
});


