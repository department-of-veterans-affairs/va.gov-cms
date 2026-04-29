import js from "@eslint/js";
import eslintConfigPrettier from "eslint-config-prettier";
import eslintConfigAirbnbExtended from "eslint-config-airbnb-extended";
import eslintPluginCypress from "eslint-plugin-cypress";

export default [
  js.configs.recommended,
  {
    plugins: {
      cypress: eslintPluginCypress,
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
      ...eslintConfigAirbnbExtended.rules,
      ...eslintConfigPrettier.rules,
      ...eslintPluginCypress.configs.recommended.rules,
      "prettier/prettier": "error",
      "consistent-return": "off",
      "cypress/no-unnecessary-waiting": "off",
      "no-underscore-dangle": "off",
      "max-nested-callbacks": ["warn", 3],
      "import/no-mutable-exports": "warn",
      "no-plusplus": ["warn", {
        allowForLoopAfterthoughts: true,
      }],
      "no-param-reassign": "off",
      "no-prototype-builtins": "off",
      "valid-jsdoc": ["warn", {
        prefer: {
          returns: "return",
          property: "prop",
        },
        requireReturn: false,
      }],
      "no-unused-vars": "warn",
      "operator-linebreak": ["error", "after", {
        overrides: {
          "?": "ignore",
          ":": "ignore",
        },
      }],
    },
  },
];

