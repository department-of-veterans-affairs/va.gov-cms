const Metalsmith = require('metalsmith');
const debug = require('metalsmith-debug');
const graphQL = require('./custom_plugins/metalsmith-graphql');

Metalsmith(__dirname)
    .source('./src')
    .destination('./build')
    .clean(true)
    .use(debug())
    .use(graphQL())
    .build(function(err) { // build
        if (err) {
            throw err;
        } // error handling
    });