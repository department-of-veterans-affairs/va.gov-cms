const axeBuilder = require('@axe-core/webdriverjs');
const webDriver = require('selenium-webdriver');
const {By} = require('selenium-webdriver');
const AxeReports = require('axe-reports');

const URL = process.env.DRUPAL_ADDRESS;
// user with administrator role
const userName = 'axcsd452ksey';
const password = 'drupal8';

let totalViolations =0;

// create a phantomjs or chrome WebDriver instance
const driver = new webDriver.Builder()
    .forBrowser('phantomjs')
    .build();

// initiate axe-webdriverjs
const AXE_BUILDER = new axeBuilder(driver)
    .withTags(['wcag2a', 'wcag2aa']);

// this is the array list of page paths after login. More pages can be added inside array at any time
const paths = [
    '/sections',
    '/node/add/page',
    '/node/add/landing_page',
    '/node/add/documentation_page',
    '/node/add/event',
    '/node/add/health_care_local_facility',
    '/node/add/health_care_region_detail_page',
    '/node/add/health_care_region_page',
    '/node/add/office',
    '/node/add/outreach_asset',
    '/node/add/person_profile',
    '/node/add/press_release',
    '/node/add/regional_health_care_service_des',
    '/node/add/news_story',
    '/node/add/support_service',
    '/user',
];

driver.get(URL)
    .then(() => {
        AXE_BUILDER
            .analyze((err, results) => {
                if (err) {
                    console.error(err);
                    return;
                }

                totalViolations = totalViolations + results.violations.length;
                AxeReports.processResults(results, 'csv', './tests/accessibility/axeReport/aXeAccessibilityCheckReport', true);
                console.log('!!!  NUMBER OF NEW VIOLATIONS on ' + URL + ' ' + results.violations.length);
                driver.findElement(By.name('name')).sendKeys(userName);
                driver.findElement(By.name('pass')).sendKeys(password);
                driver.findElement(By.name('op')).click()
                    .then(() => {
                        for (let i = 0; i < paths.length; i++) {
                            driver.get(URL + paths[i])
                                .then(() => {
                                    return AXE_BUILDER
                                        .analyze((err, results) => {
                                            if (err) {
                                                console.error(err);
                                                return;
                                            }

                                            totalViolations = totalViolations + results.violations.length;
                                            AxeReports.processResults(results, 'csv', './tests/accessibility/axeReport/aXeAccessibilityCheckReport');
                                            if (results.violations.length) {
                                                console.log('!!!  NUMBER OF NEW VIOLATIONS on ' + URL + paths[i] + '  = ' + results.violations.length);
                                                results.violations.forEach((violation) => {
                                                    console.log(violation.nodes);
                                                });
                                                return console.log(results.violations);
                                            } else {
                                                console.log('No new violations on ' + URL + paths[i]);
                                            }

                                            return;
                                        });
                                });
                        }
                    })
                    .then(() => {
                        if (totalViolations > 0) {
                            console.log('!!!  VIOLATION TYPES FOUND: ' + totalViolations + ' PROCESS EXITED WITH CODE 1  !!!');
                            driver.quit()
                                .then(() => {
                                    process.exit(1);
                                });
                        } else {
                            console.log('!!! NO ACCESSIBILITY VIOLATIONS !!!');
                            driver.quit();
                        }
                    });

            });
    });
