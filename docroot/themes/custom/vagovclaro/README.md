# vagovclaro (updated drupal admin theme)

**!! this updated admin theme is currently a work in progress. things subject to change with little notice !!**
**this is fine because it is not enabled by default, no editors are aware this theme exists. this is a safe space to move fast**

## Local Development
### Sass
Composer commands have been added to the root composer.json for compiling and watching theme assets:
- `lando composer va:theme:compile` will compile all theme assets. For core & all custom themes.
- `lando composer va:theme:watch` will run `yarn watch` to watch vagovclaro styles. Changing any scss file will trigger
a recompile & a clear of drupal caches. Reload your browser to see your changes.

Local commands:
- `yarn install` in this directory which contains a gulp workflow for sass, similar to the existing vagovadmin theme.
- `yarn build` to build the compiled css for higher environments. These files are .gitignored, and get compiled as part of the normal CI build process.
- `yarn watch` to watch & recompile during local development. drupal caches are cleared as part of this.

When including images in css rules, routes should be relative from the compiled css destination (`vagovclaro/dist/`)

### Javascript
the lando js workflow (`lando npm run build:js` or `lando npm run watch:js`, commands found in the repo's top-level package.json)
will build and transpile js to drupal specs but does not touch css styles at all. If you add JS to the theme files,
you need to run this workflow in order to correctly transpile.

## sass structure
files beginning with an _ should be included into the larger `styles.scss` file to ensure styles are compiled
files without an _ are compiled as standalone files (for smaller stylesheets included as part of a drupal library or ckeditor)

### CSS Properties
Instead of using Sass variables (`$color-foo`) we are using CSS Properties (`var(--color-foo)`). This is because we are
leveraging Claro's (`patches/claro-css-tweaks.patch`). Our theme loads its stylesheets after claro has loaded.
this gives us the ability to override variables set by claro and filter down, without targeting hyperspecific selectors
in order to override (may still be necessary in cases).

**sass variables do not compile into css variables!**
```scss
$color-white: #fff;
--color-white: $color-white;

body {
  background-color: var(--color-white);
}
```
that will not work! the browser has no idea what the sass variable is. skip the sass variable and use
the ones provided by core & claro. create new ones as necessary.

`:root {}` level variables should be set in `assets/scss/tokens/_variables.scss`. This will ensure they
are available to all elements.
