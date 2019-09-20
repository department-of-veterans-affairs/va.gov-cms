/**
 * @file Run Axe accessibility tests on page node edit form with Nightwatch.
 */
const axeOptions = {
    timeout: 20000,
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
/**const siteUrl = process.env.TESTURL;
 const name = process.env.TESTUSERNAME;
 const pass = process.env.TESTUSERPASS;
 */
const siteUrl = "http://va-gov-cms.lndo.site";
const name = "axcsd452ksey";
const pass = "drupal8";
const contextOptions = {
    include: [['body']],
    exclude: [['#content'], ['#behavior'], ['.hidden']]
};

const exclusions = {
    exclude: [['#content'], ['#behavior']]
};

/**module.exports
    = {
   '@tags': ['accessibility'],

    'Test Create Benefits detail page':  function(browser){
       browser
            .url(siteUrl)
            .pause(1000)
            .setValue('input[name="name"]', name)
            .setValue('input[name="pass"]', pass)
            .click('input[id="edit-submit"]')
            .url(siteUrl+'/node/add/page')
            .waitForElementVisible('.page-title', 6000)
            .assert.title('Create Benefits detail page | VA CMS')
            .pause(5000)
            .initAccessibility()
            .assert.accessibility(axeOptions, contextOptions)
            .end(function(err, res){
                console.log(res, err);
            });
    },

};*/

module.exports
    = {
    '@tags': ['accessibility'],

    'Test Create Benefits detail page':  function(d){
        return d.url(siteUrl)
            .pause(5000);
           // .setValue('input[name="name"]', name)
            //.setValue('input[name="pass"]', pass)
           // .click('input[id="edit-submit"]')
           // .url(siteUrl+'/node/add/page')
           // .waitForElementVisible('.page-title', 6000)
           // .assert.title('Create Benefits detail page | VA CMS')
           // .pause(5000);

    },

    'Enter user name': function(d){
        return d.setValue('input[name="name"]', name);
    },

    'Enter password': function(webdriver){
        return webdriver.setValue('input[name="pass"]', pass);
    },

    'Click enter': function(webdriver){
        return webdriver.click('input[id="edit-submit"]');
    },

    'Got to /node/add/page': function(webdriver){
        return webdriver.url(siteUrl+'/node/add/page');
    },

    'Wait for page': function(webdriver){
        return webdriver.waitForElementVisible('.page-title', 6000);
    },

    'Verify title': function(webdriver){
        return webdriver.assert.title('Create Benefits detail page | VA CMS')
    },

    'Insert aXe': function(browser) {
        return browser
            .initAccessibility()
            .pause(5000);
       // return console.log("!!!   AXE IS INJECTED   !!!");
},
    'inspect web with axe': function(browser){
        browser
           .assert.accessibility(axeOptions, 'body')
           .end(function(err, res){
               console.log(res, err);
           });
},

};

