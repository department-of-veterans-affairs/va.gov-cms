#Axe-core Accessibility Tests

## Overview 
This Accessibility automation testing framework using `axe-core` as a primary 508 accessibility validator and PhantomJS as a browser,
 and checks for `wcag2a` and `wcag2aa` standards(those can be modified inside `./tests/accessibility/aXeAccessibilityCheck.js line:20` script).

##Technology
`axe-core` accessibility validator, more information can be found at - https://www.deque.com/axe/ 
<br>

`axe-webdriverjs` `selenium-webdriver`  `phantomjs` more information can be found at - https://www.sitepoint.com/automated-accessibility-checking-with-axe/
<br>

`axe-reports` csv report generator, more information can be found at - https://www.npmjs.com/package/axe-reports. new CSV report saved after each run and can de found 
in `.test/accessibility/axeReport/` folder.


#Before tests

##Before installation
##### First
Make sure you you have NodeJS installed, to do so open your terminal and run`node --version` command, and you should see `example: v12.10.0` as a response.
If NodeJS is not installed please install it from `https://nodejs.org/en/download/`or use framework native node.
##### Second
Make sure you have NPM installed by running `npm --version` command in terminal and you should see `example: 6.11.3` as a response, if no NPM installed please install it from `https://www.npmjs.com/get-npm`.
   
## Installation
All required libraries are included inside package.json file in root. You will have to run `npm install` command in root to install all needed dependencies.  


# Running tests

##### First
Start CMS environment locally by running `lando start` command.

##### Second
To run tests locally from the commend line use `npm test` command.

##### Third 
After tests finished you can check or export `aXeAccessibilityCheckReport.csv` report that can be found inside `.test/accessibility/axeReport` folder for more detailed information.

##### Note
Accessibility test run with `phantomjs` headless browser and will not be visible. If you want to see execution of test in browser locally, 
please install chrome drive  by using `npm install chromedriver` command or by other options that can be found at - https://www.npmjs.com/package/chromedriver and then replace `phantomjs` with `chrome`  inside `./tests/accessibility/aXeAccessibilityCheck.js line:15`.
c
#To Do
Add more URL paths to: `./tests/accessibility/aXeAccessibilityCheck.js line:23` for complete coverage.

Look at the options and add assertion library into the tests to verify page is loaded or script navigated to correct page to make sure axe validates correct page.  



