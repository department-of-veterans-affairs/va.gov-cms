/* eslint-disable global-require */
/* eslint-disable import/extensions */
// eslint-disable-next-line import/no-extraneous-dependencies
const { defineConfig } = require("cypress");

module.exports = defineConfig({
  defaultCommandTimeout: 10000,
  downloadsFolder: "tests/cypress/downloads",
  env: {
    failSilently: false,
  },
  fixturesFolder: "tests/cypress/fixtures",
  retries: {
    runMode: 2,
    openMode: 0,
  },
  screenshotsFolder: "docroot/cypress/screenshots/actual",
  trashAssetsBeforeRuns: true,
  videoCompression: false,
  videoUploadOnPasses: false,
  videosFolder: "docroot/cypress/videos",
  viewportHeight: 900,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require("./tests/cypress/plugins/index.js")(on, config);
    },
    baseUrl: "http://va-gov-cms.ddev.site",
    specPattern: "tests/cypress/integration/**/*.{feature,features}",
    supportFile: "tests/cypress/support/index.js",
  },
});
