
// This test is same as ./tests/nightwatch/features/accessibilityCreateBenefitsDetailPage.js but done with axe-webdriverjs
// and selenium-webdriver instead of nightwatch and nightwatch-accessibility
// rest of the tests can be rewrittenmin smae mmanner as this one

const axeBuilder = require('axe-webdriverjs');
const webDriver = require('selenium-webdriver');
const {Builder, By, until} = require('selenium-webdriver');
const AxeReports = require('axe-reports');

// create a PhantomJS WebDriver instance
const driver = new webDriver.Builder()
    .forBrowser('phantomjs')
    .build();

// The code bellow will run Axe test and only outrint errors in console/terminal
/**driver.get('http://va-gov-cms.lndo.site')
  // driver.findElement(By.name('name')).sendKeys('axcsd452ksey');
   // driver.findElement(By.name('pass')).sendKeys('drupal8');
   // driver.findElement(By.name('op')).click();
   // driver.get('http://va-gov-cms.lndo.site/node/add/page')
    .then(() => {
        axeBuilder(driver)
           // .withTags(['wcag2a', 'wcag2aa'])
            //.withRules(['color-contrast'])
            .analyze((results) => {
                console.log(results.violations);
                driver.quit();
            });

    }); */

// The code bellow will run 508 axe test and will save detailed csv report into .tests/
driver.get('http://va-gov-cms.lndo.site');
driver.findElement(By.name('name')).sendKeys('axcsd452ksey');
driver.findElement(By.name('pass')).sendKeys('drupal8');
driver.findElement(By.name('op')).click();
driver.get('http://va-gov-cms.lndo.site/node/add/page')
    .then(() => {
        axeBuilder(driver)
            .withTags(['wcag2a', 'wcag2aa'])
            .withRules(['color-contrast'])
            .analyze((results) => {
                AxeReports.processResults(results, 'csv', './tests/accessibility/reports/aXe-test-results', true);
                driver.quit();
            });

    });