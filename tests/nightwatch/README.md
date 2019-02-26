# Nightwatch Accessibility Tests

## Installation
Required libraries are included in package.json in root. You may have to run `npm install` in root to install dependencies. 

These tests use nightwatch, a front end testing tool and nightwatch-accessibliity a wrapper for aXe. 

Documentation can be found at 

Nightwatch - http://nightwatchjs.org
nightwatch-accessibility - https://github.com/ahmadnassri/nightwatch-accessibility 
aXe - https://www.deque.com/axe/documentation

## Running tests
From the commend line 

`npm run-script nightwatch --test <path-to-test>`
 
or

`npm run-script nightwatch --tag <tag>`

for example

`npm run-script nightwatch --tag accessibility`


