const Metalsmith = require('metalsmith');
const debug = require('metalsmith-debug');
const getBasicPages = require('./custom_plugins/metalsmith-getbasicpage');

Metalsmith(__dirname)
    .source('./src')
    .destination('./build')
    .clean(true)
    .use(debug())
    .use(getBasicPages())
    .build(function(err) { // build
        if (err) {
            throw err;
        } // error handling
    });