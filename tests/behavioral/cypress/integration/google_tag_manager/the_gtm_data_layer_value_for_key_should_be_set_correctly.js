import { Then } from "cypress-cucumber-preprocessor/steps";

const aliasMap = {
  'nodeID': '@nodeId',
  'pagePath': '@pagePath',
};

Then(`the GTM data layer value for {string} should be set correctly`, (key) => {
  const alias = aliasMap[key];
  assert.isNotNull(alias, `I do not know how to check correctness for "${key}".  Please add a definition in ${__filename}.`);
  cy.getDataLayer()
    .then((dataLayer) => cy.wrap(dataLayer[key]))
    .then((actual) => cy.get(alias).should('eq', actual));
});

