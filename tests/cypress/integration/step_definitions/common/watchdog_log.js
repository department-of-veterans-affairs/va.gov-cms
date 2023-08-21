import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(
  `the watchdog log should not contain new {string} messages`,
  function watchdogLogHandler(severity) {
    return cy.drupalWatchdogHasNoNewMessages(this.username, severity);
  }
);

Given(
  `the watchdog log should not contain new errors`,
  function watchdogLogHandler() {
    return cy.drupalWatchdogHasNoNewMessages(this.username, "Error");
  }
);

Given(
  `the watchdog log should contain {int} new {string} messages`,
  function watchdogLogHandler(count, severity) {
    return cy.drupalWatchdogHasNewMessages(this.username, severity, count);
  }
);

Given(
  `the watchdog log should contain {int} new errors`,
  function watchdogLogHandler(count) {
    return cy.drupalWatchdogHasNewMessages(this.username, "Error", count);
  }
);
