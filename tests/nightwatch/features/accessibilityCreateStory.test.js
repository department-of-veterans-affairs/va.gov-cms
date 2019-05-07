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
    reporter: 'v2',
    elementRef: true,
    abortOnAssertionFailure: false,
    end_session_on_fail: false,
    skip_testcases_on_fail: true,
};

// Environmental variables must be set before running test
const siteUrl = process.env.TESTURL;
const name = process.env.TESTUSERNAME;
const pass = process.env.TESTUSERPASS;

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

    'Test Create Story': function (browser) {
        browser
            .url(siteUrl)
            .setValue('input[name="name"]', name)
            .setValue('input[name="pass"]', pass)
            .click('input[id="edit-submit"]')
            .url(siteUrl+'/node/add/news_story')
            .waitForElementVisible('.page-title', 6000)
            .assert.title('Create Story | Veterans Affairs')
            .initAccessibility()
            .verify.accessibility(contextOptions, axeOptions)
            .end(function(err, res){
                console.log(res);
            });
    }
}
