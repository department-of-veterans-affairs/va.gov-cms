const { TwingEnvironment, TwingLoaderRelativeFilesystem } = require('twing');
const { addDrupalExtensions } = require('drupal-twig-extensions/twing');

const twing = new TwingEnvironment(
  new TwingLoaderRelativeFilesystem(), {autoescape: false}
);

// See: https://www.npmjs.com/package/drupal-twig-extensions for details.
addDrupalExtensions(twing);

module.exports = twing;
