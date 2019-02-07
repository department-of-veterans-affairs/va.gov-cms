/**
 * @file Run Axe accessibility tests with Nightwatch.
 */
// import "nightwatch-accessibility";

module.exports = {
    '@tags': ['accessibility'],
    'Axe Test': function (browser) {
        browser
            .url('http://vagovcms.lndo.site') // Navigate to the url
            .waitForElementVisible('body', 2000) // Wait until you can see the body element.
            .initAccessibility()
            .assert.accessibility('body', {
            verbose: true,
            rules: {
                'color-contrast': { enabled: false }
            }
        })
            .end()
    }
}
