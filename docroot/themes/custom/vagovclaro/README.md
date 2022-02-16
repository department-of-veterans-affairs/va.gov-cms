# VAgovClaro

This is the new CMS admin theme for editors entering content for VA.gov.

It is based on Drupal Core's Claro theme.

## Local Development
### Sass
Composer commands have been added to the root composer.json for compiling and watching theme assets. Run these prefixed
with `ddev` or `lando` to run inside your local development container.
- `composer va:theme:compile` will compile all theme assets. For core & all custom themes.
- `composer va:theme:watch` will run `yarn watch` to watch vagovclaro styles. Changing any scss file will trigger
a recompile & a clear of drupal caches. Reload your browser to see your changes.

Local commands:
- `yarn install` in this directory which contains a gulp workflow for sass, similar to the existing vagovadmin theme.
- `yarn build` to build the compiled css for higher environments. These files are .gitignored, and get compiled as part of the normal CI build process.
- `yarn watch` to watch & recompile during local development. drupal caches are cleared as part of this.

When including images in css rules, routes should be relative from the compiled css destination (`vagovclaro/dist/`)

### Javascript
The javascript workflow is still controlled by npm commands found in the repo's top-level package.json.
Running `npm run build:js` from that package.json will build and transpile javascript across the project. Both lando and
ddev have mappings for the top-level package.json, so `ddev npm run build:js` will do what you need.

If you add new javascript to an `*.es6.js` file in  `/assets/js`, you need to run the build command in order to
correctly transpile for all browsers.

## sass structure
Files beginning with an _ should be included into the larger `styles.scss` file to ensure styles are compiled.
Files without an _ are compiled as standalone files (for smaller stylesheets included as part of a separate Drupal library or ckeditor)

### CSS Properties
Instead of using Sass variables (`$color-foo`) we are using CSS Properties (`var(--color-foo)`). This is because we are
leveraging Claro's (`patches/claro-css-tweaks.patch`). Our theme loads its stylesheets after Claro has loaded.
This gives us the ability to override variables set by Claro and filter down, without targeting hyperspecific selectors
in order to override (may still be necessary in cases).

`:root {}` level variables should be set in `assets/scss/tokens/_variables.scss`. This will ensure they
are available to all elements. Variables can be still be overridden on a component basis, if necessary. This may
become more common as we begin implementing the new design system beyond recoloring Claro.
