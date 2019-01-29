// Get Selenium and the drivers
var seleniumServer = require('selenium-server');
var chromedriver = require('chromedriver');
var geckodriver = require('geckodriver');

var config = {
  "src_folders" : [
    // Folders with tests
    "tests/visual"
  ],

  "webdriver" : {
    "start_process": true
  },

  "test_settings" : {
    "default" : {
      "webdriver": {
        "server_path": "./node_modules/.bin/geckodriver",
        "port": 4444,
        "cli_args": [
          "--log", "debug"
        ]
      },
      "desiredCapabilities" : {
        "browserName" : "firefox",
        "acceptInsecureCerts" : true
      }
    },

    "chrome" : {
      "webdriver": {
        "port": 9515,
        "server_path": "./node_modules/.bin/chromedriver",
        "cli_args": [
          "--verbose"
        ]
      },

      "desiredCapabilities" : {
        "browserName" : "chrome",
        "loggingPrefs": {"driver": "INFO", "server": "OFF", "browser": "INFO"}
      }
    },

    "selenium_server" : {
      "selenium" : {
        "start_process": true,
        "host": "localhost",
        "server_path": "selenium-server-standalone-3.141.59.jar",
        "cli_args": {
          "webdriver.gecko.driver": "./node_modules/.bin/geckodriver",
          "webdriver.chrome.driver": "./node_modules/.bin/chromedriver"
        }
      },

      "desiredCapabilities" : {
        "browserName" : "firefox",
        "acceptSslCerts": true
      }
    }
  }
};

module.exports = config;