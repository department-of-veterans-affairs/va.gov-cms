/**
 * @file
 * An example of running Pa11y programmatically, reusing existing Puppeteer browsers and pages.
 */

'use strict';

const pa11y = require('pa11y');
const puppeteer = require('puppeteer');

runPa11y();

// Async function required for us to use await.
async function runPa11y() {
  let browser;
  let pages = [];
  let log;

  // Add URLs to check to this array.
  let urls = [
    '/',
    '/careers/openings'
  ];

  let results = [];

  try {

    // Launch our own browser.
    browser = await puppeteer.launch({
      executablePath: '/usr/bin/google-chrome',
      // Drone doesn't allow changing user, so we need to disable the sandbox.
      args: ['--no-sandbox']
    });

    // Create a page for the test runs (Pages cannot be used in multiple runs).
    for (var i = 0; i < urls.length; i++) {
      pages[i] = await browser.newPage();
    }

    log = {
      debug: console.log,
      error: console.error,
      info: console.log
    }
    const credentials = 'nsf:nsfbeta';
    const encodedCredentials = new Buffer(credentials).toString('base64');

    for (var j = 0; j < urls.length; j++) {
      results[j] = await pa11y('http://web' + urls[j], {
        browser: browser,
        standard: 'Section508',
        headers: {
          Authorization: 'Basic ' + encodedCredentials
        },
        page: pages[j],
        log: log,
        ignore: [
          'Section508.undefined.F92,ARIA4'
        ]
      });

      // Output the raw result objects to console.
      console.log(results[j]);
    }
    // Close the browser instance and pages now we're done with it.
    for (const page of pages) {
      await page.close();
    }
    await browser.close();

  }
  catch (error) {
    // Output an error if it occurred.
    console.error(error.message);

    // Close the browser instance and pages if they exist.
    if (pages) {
      for (const page of pages) {
        await page.close();
      }
    }
    if (browser) {
      await browser.close();
    }

    for (var k = 0; k < urls.length; k++) {
      if (results[k].issues.length > 0) {
         // If the test failed exit non-zero.
        await process.exit(1);
      }
    }
  }
}
