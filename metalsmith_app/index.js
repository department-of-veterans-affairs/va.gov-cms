const Metalsmith = require('metalsmith');
const inplace = require('metalsmith-in-place');
const debug = require('metalsmith-debug');

Metalsmith(__dirname)
    .source('./src')
    .destination('./build')
    .clean(true)
    .use(debug())
    .use(request({
        drupal: 'http://vagovcms.lndo.site/jsonapi/node/page'
    }))
    .use(inplace())
    .build(function(err) { // build
        if (err) {
            throw err;
        } // error handling
    });