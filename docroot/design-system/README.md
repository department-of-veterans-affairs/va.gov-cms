# CMS Design System

This directory contains components for the CMS design system. With a future in mind where this design system
may be used by more than the VA.gov backend, the design system tooling & component templates are located in this
directory, separately from our custom Drupal themes. So Storybook and associated tooling lives here for now.
These components are imported by the Drupal admin theme `vagovclaro` in that theme's templates.
Relevant compiled js + stylesheets are imported in the theme's libraries.yml file.

## Quickstart
`ddev Storybook` to work on design system components in Storybook locally.
Ignore the output about localhost, that's inside the container. Visit https://va-gov-Storybook.ddev.site/ instead

## Commands
- `yarn run build:Storybook` compiles Storybook for tugboat

- `yarn run build:drupal` compiles design system components (without Storybook code) for Drupal.

Those commands are available in the container as well, through scripts in composer.json. They are named `composer va:ds:Storybook` and `composer va:ds:drupal` respectively.

- `yarn run css` is a helper command that extracts CSS Custom Properties into a .json file, for easy manipulation to display "tokens" in Storybook.

- TODO: add generator command for component boilerplate `yarn run new`

## Icons
This design system supports the free icons from [fontawesome](https://fontawesome.com/) using their [SVG Core](https://fontawesome.com/docs/apis/javascript/import-icons) package.
Look at `components/icon/index.js` for an example.
