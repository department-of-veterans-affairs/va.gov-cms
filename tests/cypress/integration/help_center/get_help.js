import { Then } from "cypress-cucumber-preprocessor/steps";

Then(/I should see(?: (a|an|the))? Get Help link/, () =>
  cy.get(".suffix-links").should("be.visible")
);

Then(/(?: (a|an|the))? Get Help link should exist/, () =>
  cy.get(".suffix-links").should("exist")
);
