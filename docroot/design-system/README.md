# CMS Design System

This directory contains components for the CMS design system. With a future in mind where this design system
may be used by more than the va.gov backend, the design system tooling & component templates are located in this
directory, separately from our custom Drupal themes. So Storybook and associated tooling lives here for now.
These components are imported by the Drupal admin theme vagovclaro in that theme's templates.
Relevant compiled js + stylesheets are imported in the theme's libraries.yml file.


`yarn run storybook` to work & run storybook locally

`yarn run build-storybook` to compile storybook

need a command to compile for drupal
`yarn run build-drupal`
