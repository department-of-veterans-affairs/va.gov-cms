const Metalsmith = require('metalsmith');
const debug = require('metalsmith-debug');
const graphQL = require('./custom_plugins/metalsmith-graphql');
const inPlace = require('metalsmith-in-place');

Metalsmith(__dirname)
    .metadata({
        sitename: "VA.gov Drupal Test",
        siteurl: "http://va-test.gov/",
        description: "A simple site showing off Metalsmith + Drupal integration",
        generatorname: "Metalsmith",
        generatorurl: "http://metalsmith.io/"
    })
    .source('./src')
    .destination('./build')
    .clean(true)
    .use(debug())
    .use(graphQL())
    .use(inPlace())
    .build(function(err) { // build
        if (err) {
            throw err;
        } // error handling
    });