const axeBuilder = require('axe-webdriverjs');
const webDriver = require('selenium-webdriver');
const {Builder, By, until} = require('selenium-webdriver');

const siteUrl = "http://va-gov-cms.lndo.site";
const name = "axcsd452ksey";
const pass = "drupal8";

// create a PhantomJS WebDriver instance
const driver = new webDriver.Builder()
    .forBrowser('phantomjs')
    .build();

driver.get('http://va-gov-cms.lndo.site');
   driver.findElement(By.name('name')).sendKeys('axcsd452ksey');
    driver.findElement(By.name('pass')).sendKeys('drupal8');
    driver.findElement(By.name('op')).click();
    driver.get('http://va-gov-cms.lndo.site/node/add/page')
    .then(() => {
        axeBuilder(driver)
            .withTags(['wcag2a', 'wcag2aa'])
            //.withRules(['color-contrast'])
            .analyze((results) => {
                console.log(results.violations);
                driver.quit();
            });

    });