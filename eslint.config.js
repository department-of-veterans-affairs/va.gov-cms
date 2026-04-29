const js = require("@eslint/js");
const globals = require("globals");
const eslintConfigPrettier = require("eslint-config-prettier");
const eslintPluginCypress = require("eslint-plugin-cypress");
const eslintPluginImport = require("eslint-plugin-import");
const eslintPluginPrettier = require("eslint-plugin-prettier");

module.exports = [
  js.configs.recommended,
  {
    linterOptions: {
      reportUnusedDisableDirectives: "off",
    },
    plugins: {
      import: eslintPluginImport,
      prettier: eslintPluginPrettier,
    },
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: "module",
      globals: {
        ...globals.browser,
        ...globals.node,
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
        once: "readonly",
      },
    },
    rules: {
      "prettier/prettier": "error",
      "consistent-return": "off",
      "import/no-mutable-exports": "warn",
      "no-underscore-dangle": "off",
        "eqeqeq": ["error", "always", { null: "ignore" }],
        "curly": ["warn", "all"],
        "radix": "error",
      "no-alert": "warn",
      "no-eval": "warn",
      "no-implied-eval": "warn",
      "no-new-func": "warn",
      "no-throw-literal": "warn",
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
  {
    ...eslintPluginCypress.configs.recommended,
    files: ["tests/cypress/**/*.js"],
    rules: {
      ...eslintPluginCypress.configs.recommended.rules,
      "cypress/no-unnecessary-waiting": "off",
    },
  },
  eslintConfigPrettier,
];
