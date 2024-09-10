import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I check the watchdog log`, function watchdogLogHandler() {
  cy.drupalGetWatchdogMessages(this.username).then(cy.log);
});

Given(
  `I check the watchdog log for {string} messages`,
  function watchdogLogHandler(severity) {
    cy.drupalGetWatchdogMessages(this.username, severity).then(cy.log);
  }
);
