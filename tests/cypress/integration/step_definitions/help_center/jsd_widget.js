import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(/(?: (a|an|the))? JSD widget should exist/, () =>
  cy.get("iframe#jsd-widget").iframe().should("exist")
);
Then(/(?: (a|an|the))? JSD widget should not exist/, () =>
  cy.get("iframe#jsd-widget").should("not.exist")
);
