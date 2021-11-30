# vagovclaro (updated drupal admin theme)

**!! this updated admin theme is currently a work in progress. things subject to change with little notice !!**
**this is fine because it is not enabled by default, no editors are aware this theme exists. this is a safe space to move fast**

## context

- we are building on Drupal's Claro theme, pulling in & updating existing templates from vagovadmin.
- initial buildout will be mostly a 1:1 copy with necessary updates
- update to new tokens etc. when they are defined by design
- pattern lab?


MVP TO TURN ON FOR EDITORS:
- login page with SSO
- knowledge base
- some level of VA branding. logo on login, color scheme, etc.
- feature parity with existing theme in terms of form functionality.



## local development

`npm install` in this directory, the lando js workflow (`lando npm run build:js` or `lando npm run watch:js`) will build
and transpile js to drupal specs but does not touch css styles at all. this directory contains a gulp workflow, similar to
the existing vagovadmin theme. you need to run gulp separately in order to keep styles updated.

`npm run build` to build the compiled css for higher environments (this needs to be committed)

`npm run build:watch` to watch & recompile during local development

## sass structure
files beginning with an _ should be included into the larger styles.scss file to ensure styles are compiled
files without an _ are compiled as standalone files (for smaller stylesheets included as part of a drupal library or ckeditor)
