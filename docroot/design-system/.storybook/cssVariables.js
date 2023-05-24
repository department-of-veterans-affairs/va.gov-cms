/*
 * Helper script to parse out CSS Custom Properties from the :root object.
 *
 * This outputs a .json file in the design-system/.storybook/ folder. It is used by tokens.stories.js
 * to display css variables in Storybook without duplicating them in JS & keep a single source of truth.
 */
const postcss = require('postcss');
const postcssExtract = require('@csstools/postcss-extract');
const fs = require('fs');
const path = require('path');

fs.readFile(path.resolve(__dirname,'../components/tokens/_variables.scss'), (err, css) => {
  postcss({
    plugins: [
      postcssExtract({
        queries: {
          'custom-properties': 'rule[selector*=":root" i] > decl[variable]'
        },
        results: function(results) {
          let finalResults = {
            'custom-properties': {}
          };
          results = results['custom-properties'].map(r => ({
              [r.prop]: r.value
          }));
          for(let i = 0; i < results.length; i++ ) {
            Object.assign(finalResults["custom-properties"], results[i]);
          }
          fs.writeFile("./.storybook/cssVariables.json", JSON.stringify(finalResults, null, 2), (err) => {
            if (err) {
                console.error(err);
                return;
            };
            console.log("cssVariables.json has been created");
        });
        }
      })
    ]
  }).process(css, {
    from: path.resolve(__dirname, '../components/tokens/_variables.scss'),
    to: '' // don't need a css file here, just want the .json output frm above
  }).then(result => {
    console.log('Finished processing CSS variables from ' + result.opts.from + ' for Storybook!');
  })
});
