/**
 * @file Run Axe accessibility tests on page node edit form with Nightwatch.
 */

const axeOptions = {
    timeout: 500,
    runOnly: {
        type: 'tag',
        values: ['wcag2a'], //, 'wcag2aa'
    },
    verbose: true,
    // exclude: [['#fix div']],
    reporter: 'v2',
    elementRef: true,
    abortOnAssertionFailure: false,
    end_session_on_fail: false,
    skip_testcases_on_fail: true,
};

const dev = "http://vagovcms.lndo.site";
const siteUrl = dev;
const name = 'admin';
const pass = 'drupal8';

const contextOptions = {
    include: [['body']],
    exclude: [['#content'], ['#behavior'], ['.hidden']]
}

const exclusions = {
    exclude: [['#content'], ['#behavior']]
}

module.exports
    = {
    '@tags': ['accessibility'],

    'Test add node': function (browser) {
        browser
            .url(siteUrl)
            .setValue('input[name="name"]', name)
            .setValue('input[name="pass"]', pass)
            .click('input[id="edit-submit"]')
            // todo get current environment url
            .url(siteUrl+'/node/add/page')
            .waitForElementVisible('body', 6000)
            .initAccessibility()
            .verify.accessibility(contextOptions, axeOptions)
            .end(function(err, res){
                console.log(res);
            });
    }
}
