/* eslint-disable import/no-unresolved */
/* eslint-disable no-console */
/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable import/extensions */
import { defineConfig } from "cypress";
import cucumber from "@badeball/cypress-cucumber-preprocessor";
import getCompareSnapshotsPlugin from "cypress-visual-regression/dist/plugin";
import createBundler from "@bahmutov/cypress-esbuild-preprocessor";
import { createEsbuildPlugin } from "@badeball/cypress-cucumber-preprocessor/esbuild";
import cypressFailedLog from "cypress-failed-log/on";
import fs from "fs";
import path from "path";
import pixelmatch from "pixelmatch";
import { PNG } from "pngjs";
// This function is called when a project is opened or re-opened (e.g. due to
// the project's config changing)

const BASE_URL = process.env.BASE_URL || "https://va-gov-cms.ddev.site";

async function setupNodeEvents(on, config) {
  await cucumber.addCucumberPreprocessorPlugin(on, config);
  await getCompareSnapshotsPlugin(on, config);
  await cypressFailedLog(on, config);

  on('before:browser:launch', (browser, launchOptions) => {
    console.log(`Launching browser: ${browser.name} (${browser.family})`);
    if (browser.family === 'chromium') {
      launchOptions.args.push('--disable-dev-shm-usage');
      launchOptions.args.push('--no-sandbox');
      launchOptions.args.push('--disable-setuid-sandbox');
      launchOptions.args.push('--disable-gpu');
      launchOptions.args.push('--disable-software-rasterizer');
      launchOptions.args.push('--disable-extensions');
      launchOptions.args.push('--disable-background-timer-throttling');
      launchOptions.args.push('--disable-backgrounding-occluded-windows');
      launchOptions.args.push('--disable-renderer-backgrounding');
      launchOptions.args.push('--disable-features=IsolateOrigins,site-per-process');
      launchOptions.args.push('--disable-ipc-flooding-protection');
      launchOptions.args.push('--js-flags=--expose-gc');
      launchOptions.args.push('--force-color-profile=srgb');
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
    pixelmatchCompare({ derivativeBase64, fixtureBase64, testTitle }) {
      const derivativeImage = PNG.sync.read(Buffer.from(derivativeBase64, "base64"));
      const { width, height } = derivativeImage;
      const fixtureImage = PNG.sync.read(Buffer.from(fixtureBase64, "base64"));
      const diff = new PNG({ width, height });
      const differences = pixelmatch(
        fixtureImage.data,
        derivativeImage.data,
        diff.data,
        width,
        height
      );
      const diffData = PNG.sync.write(diff);
      const diffDir = path.join(process.cwd(), "cypress/screenshots/pixelmatch_diffs");
      if (!fs.existsSync(diffDir)) {
        fs.mkdirSync(diffDir, { recursive: true });
      }
      fs.writeFileSync(path.join(diffDir, `${testTitle}.png`), diffData);
      return { differences, width, height };
    },
  });

  on("file:preprocessor", createBundler({
    plugins: [createEsbuildPlugin(config)],
    define: {
      __filename: '"unknown"',
      __dirname: '"unknown"',
    },
  }));

  return config;
}

export default defineConfig({
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
  video: false,
  videosFolder: "docroot/cypress/videos",
  viewportHeight: 900,
  viewportWidth: 1000,
  e2e: {
    setupNodeEvents,
    baseUrl: BASE_URL,
    specPattern: "tests/cypress/integration/features/**/*.{feature,features}",
    supportFile: "tests/cypress/support/index.js",
  },
});
