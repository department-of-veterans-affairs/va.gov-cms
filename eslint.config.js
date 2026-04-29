const js = require("@eslint/js");
const eslintConfigPrettier = require("eslint-config-prettier");
const eslintPluginCypress = require("eslint-plugin-cypress");
const eslintPluginPrettier = require("eslint-plugin-prettier");

module.exports = [
  js.configs.recommended,
  {
    plugins: {
      cypress: eslintPluginCypress,
      prettier: eslintPluginPrettier,
    },
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: "module",
      globals: {
        Drupal: "readonly",
        drupalSettings: "readonly",
        drupalTranslations: "readonly",
        domready: "readonly",
        jQuery: "readonly",
        _: "readonly",
        matchMedia: "readonly",
        Cookies: "readonly",
        Backbone: "readonly",
        Modernizr: "readonly",
        Popper: "readonly",
        Sortable: "readonly",
        CKEDITOR: "readonly",
      },
    },
    rules: {
      "prettier/prettier": "error",
      ...eslintPluginCypress.configs.recommended.rules,
      "consistent-return": "off",
      "cypress/no-unnecessary-waiting": "off",
      "no-underscore-dangle": "off",
      "max-nested-callbacks": ["warn", 3],
      "no-plusplus": ["warn", {
        allowForLoopAfterthoughts: true,
      }],
      "no-param-reassign": "off",
      "no-prototype-builtins": "off",
      "no-unused-vars": "warn",
      "operator-linebreak": ["error", "after", {
        overrides: {
          "?": "ignore",
          ":": "ignore",
        },
      }],
    },
  },
  eslintConfigPrettier,
];
