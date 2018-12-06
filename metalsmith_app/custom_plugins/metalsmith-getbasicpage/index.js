/*global __dirname, require, console, module, plugin, metalsmith, setImmediate, error, response, data, process, writeFileSync*/
/*jshint esversion: 6 */


const request = require('request');
const debug = require('metalsmith-debug')('metalsmith-getbasicpage');


/**
 * Metalsmith plugin to prepare a yml data file from api data
 */

// Expose Plugin
module.exports = plugin;

function plugin(opts) {

    const getBasicPageQuery = (siteUrl, endPoint) => {
        return siteUrl + endPoint;
    };

    opts.pattern = opts.pattern || [];

    return function(files, metalsmith, done) {
        const siteUrl = 'http://vagovcms.lndo.site';
        const endPoint = '/jsonapi/node/page';
        const drupalRequest = getBasicPageQuery(siteUrl, endPoint);

        // get data from Lando Drupal instance
        request.get(drupalRequest, function(error, response, data) {
            if(error) {
                console.log(error);
            }

            const pageDataObj = JSON.parse(data);
        });

    };

}