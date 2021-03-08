import { Then } from "cypress-cucumber-preprocessor/steps";

const aliasMap = {
  'nodeID': '@nodeId',
  'pagePath': '@pagePath',
};

Then(`the GTM data layer value for {string} should be set correctly`, (key) => {
  const alias = aliasMap[key];
  assert.isNotNull(alias, `I do not know how to check correctness for "${key}".  Please add a definition in ${__filename}.`);
  cy.getDataLayer().then((dataLayer) => {
    cy.get(alias).then((expected) => {
      const actual = dataLayer[key];
      cy.wrap(actual).should('eq', expected);
    });
  });
});

