/*global __dirname, require, console, module, plugin, metalsmith, setImmediate, error, response, data, process, writeFileSync*/
/*jshint esversion: 6 */


const request = require('request');
const debug = require('metalsmith-debug')('metalsmith-getbasicpage');


/**
 * Metalsmith plugin to prepare a yml data file from api data
 */
function plugin() {

    const getBasicPageQuery = (siteUrl, endPoint) => {
        return siteUrl + endPoint;
    };

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
            pageDataObj.siteUrl = siteUrl;

            let pagesSummary = {};
            let temp = {};

            pageDataObj.data.forEach(function (page) {
                temp = {};
                temp.pageTitle = page.attributes.title;
                temp.introText = page.attributes.field_intro_text;

                pagesSummary[page.id] = temp;
            });

            console.log(pagesSummary);

            // add blogpostSummary variables to the metalsmith metadata
            let metadata = metalsmith.metadata();
            metadata.basicPages = pagesSummary;
            metalsmith.metadata(metadata);

            done();
        });

    };
}

// Expose Plugin
module.exports = plugin;