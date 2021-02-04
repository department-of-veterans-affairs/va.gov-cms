# cypress-axe Accessibility Tests

## Overview 
This Accessibility automation testing framework using `cypress-axe` with Cypress' built-in chromium-based electron browser as a primary 508 accessibility validator, and checks for `wcag2a` and `wcag2aa` standards./tests/accessibility/aXeAccessibilityCheck.js line:20` script).

These tests only run in local Lando and on Tugboat for Pull Requests and they do not run on BRD (dev/staging) because there was an issue installing all the xorg-related dependencies on Amazon Linux 1 AMI. 
## Technology
`axe-core` accessibility validator, more information can be found at - https://www.deque.com/axe/ 

`cypress-axe` more information can be found at - https://github.com/component-driven/cypress-axe


# Before tests

## Configuration

Cypress will use the baseUrl set in `./tests/cypress.json` by default. If the `CYPRESS_BASE_URL` environment variable is set, cypress will use it preferentially.

## Before installation

### First

Make sure you you have NodeJS installed, to do so open your terminal and run `node --version` command, and you should see `example: v12.10.0` as a response.
If NodeJS is not installed please install it from `https://nodejs.org/en/download/`or use framework native node.

### Second

Make sure you have NPM installed by running `npm --version` command in terminal and you should see `example: 6.11.3` as a response, if no NPM installed please install it from `https://www.npmjs.com/get-npm`.
   
## Installation
All required libraries are included inside package.json file in root. You will have to run `npm install` command in root to install all needed dependencies.  


# Running tests

## First

Start CMS environment locally by running `lando start` command.

## Second

To run tests locally from the commend line use `npm test` command.

## Third 

Information about any errors found will be output on the console. After tests have finished you can views screenshots that can be found inside the `./tests/cypress/screenshots`. For help debugging any errors encountered during testing, you may also turn on video recording by editing `./tests/cypress.json` and setting the `video` parameter to true. Video recordings will then be placed in `./tests/cypress/videos`. 

# To Do
Add more URL paths to: `./tests/cypress/integration/nodeCreationAccessibility.spec.js line:2` for complete coverage.
