/* eslint-disable import/no-unresolved */
/* eslint-disable no-console */
/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable global-require */
/* eslint-disable import/extensions */
const { defineConfig } = require("cypress");
const cucumber = require("@badeball/cypress-cucumber-preprocessor");
const getCompareSnapshotsPlugin = require("cypress-visual-regression/dist/plugin");
const browserify = require("@badeball/cypress-cucumber-preprocessor/browserify");
const cypressFailedLog = require("cypress-failed-log/on");
const fs = require("fs");
const path = require("path");
// This function is called when a project is opened or re-opened (e.g. due to
// the project's config changing)

const BASE_URL = process.env.BASE_URL || "https://va-gov-cms.ddev.site";

async function setupNodeEvents(on, config) {
  // This is required for the preprocessor to be able to generate JSON reports after each run, and more,
  await cucumber.addCucumberPreprocessorPlugin(on, config);

  await getCompareSnapshotsPlugin(on, config);

  await cypressFailedLog(on, config);

  // Configure browser launch args for Kubernetes/container environments
  on('before:browser:launch', (browser = {}, launchOptions) => {
    console.log(`Launching browser: ${browser.name} (${browser.family})`);
    
    if (browser.name === 'electron') {
      // Electron is optimized for Cypress and works well in Kubernetes
      // No special flags needed - it's the recommended browser for CI/CD
      launchOptions.preferences = launchOptions.preferences || {};
      launchOptions.preferences.devTools = false;
    } else if (browser.family === 'chromium') {
      // Chrome/Chromium flags for containerized environments (when explicitly testing Chrome)
      console.log('Adding Chrome flags for containerized environment');
      
      // Essential flags for containerized environments
      launchOptions.args.push('--disable-dev-shm-usage');
      launchOptions.args.push('--no-sandbox');
      launchOptions.args.push('--disable-setuid-sandbox');
      launchOptions.args.push('--disable-gpu');
      
      // Additional stability flags for Kubernetes
      launchOptions.args.push('--disable-software-rasterizer');
      launchOptions.args.push('--disable-extensions');
      launchOptions.args.push('--disable-background-timer-throttling');
      launchOptions.args.push('--disable-backgrounding-occluded-windows');
      launchOptions.args.push('--disable-renderer-backgrounding');
      launchOptions.args.push('--disable-features=IsolateOrigins,site-per-process');
      
      // Memory and performance optimizations
      launchOptions.args.push('--disable-ipc-flooding-protection');
      launchOptions.args.push('--js-flags=--expose-gc');
      launchOptions.args.push('--force-color-profile=srgb');
      
      // Prevent Chrome from showing error dialogs
      launchOptions.args.push('--disable-breakpad');
      launchOptions.args.push('--disable-component-extensions-with-background-pages');
    }
    return launchOptions;
  });

  on("task", {
    log(message) {
      console.log(message);
      return null;
    },
    table(message) {
      console.table(message);
      return null;
    },
    saveHtmlToFile({ htmlContent, fileName }) {
      const dirPath = path.join(
        process.cwd(),
        "docroot/cypress/fail-html-snapshots"
      );

      if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath);
      }
      const filePath = path.join(dirPath, fileName);

      fs.writeFileSync(filePath, htmlContent, "utf8");

      const httpsUrl = filePath
        .replace(process.cwd(), BASE_URL)
        .replace("docroot/", "");

      return `HTML has been saved to ${httpsUrl}`;
    },
    readFileMaybe(filename) {
      if (fs.existsSync(filename)) {
        return fs.readFileSync(filename, "utf8");
      }
      return null;
    },
  });

  on("file:preprocessor", browserify.default(config));

  // Make sure to return the config object as it might have been modified by the plugin.
  return config;
}

module.exports = defineConfig({
  chromeWebSecurity: false,
  defaultCommandTimeout: 30000,
  downloadsFolder: "tests/cypress/downloads",
  env: {
    failSilently: false,
  },
  fixturesFolder: "tests/cypress/fixtures",
  responseTimeout: 60000,
  retries: {
    runMode: 2,
    openMode: 0,
  },
  screenshotsFolder: "docroot/cypress/screenshots/actual",
  trashAssetsBeforeRuns: false,
  videoCompression: false,
  videoUploadOnPasses: false,
  videosFolder: "docroot/cypress/videos",
  viewportHeight: 900,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents,
    baseUrl: BASE_URL,
    specPattern: "tests/cypress/integration/features/**/*.{feature,features}",
    supportFile: "tests/cypress/support/index.js",
  },
});
