import importPlugin from "eslint-plugin-import";
import eslintPluginPrettier from "eslint-plugin-prettier";
// Flat ESLint config for ESLint 9+
import js from "@eslint/js";
import globals from "globals";

// Remove any global keys with leading/trailing whitespace (ESLint 9 strict)
function cleanGlobals(obj) {
  return Object.fromEntries(
    Object.entries(obj).filter(([k]) => k.trim() === k)
  );
}

export default [
    // Project-specific globals for all JS files
    {
      files: ["**/*.js", "**/*.es6.js"],
      languageOptions: {
        ecmaVersion: 2020,
        sourceType: "module",
        globals: {
          ...cleanGlobals(globals.browser),
          ...cleanGlobals(globals.node),
          Drupal: "writable",
          drupalSettings: "writable",
          drupalTranslations: "writable",
          domready: "writable",
          jQuery: "writable",
          _: "writable",
          matchMedia: "writable",
          Cookies: "writable",
          Backbone: "writable",
          Modernizr: "writable",
          Popper: "writable",
          Sortable: "writable",
          CKEDITOR: "writable",
          loadjs: "writable",
          Shepherd: "writable",
          once: "writable",
          CKEditor5: "writable",
          tabbable: "writable",
          transliterate: "writable",
          bodyScrollLock: "writable",
          FloatingUIDOM: "writable",
        },
      },
    },
  {
    files: ["**/*.js", "**/*.es6.js"],
    plugins: {
      import: importPlugin,
      prettier: eslintPluginPrettier,
    },
    rules: {
      ...js.rules,
      // Formatting rules off (Prettier handles formatting)
      "indent": "off",
      "quotes": "off",
      "semi": "off",
      "comma-dangle": "off",
      "object-curly-spacing": "off",
      "space-before-function-paren": "off",
      "max-len": "off",
      // Prettier as error
    //   "prettier/prettier": " error", --- IGNORE ---

      // Code quality rules
      "consistent-return": "off",
      "no-underscore-dangle": "off",
      "max-nested-callbacks": ["warn", 3],
      "import/no-mutable-exports": "warn",
      "no-plusplus": ["warn", { allowForLoopAfterthoughts: true }],
      "no-param-reassign": "off",
      "no-prototype-builtins": "off",
      // "valid-jsdoc": ["warn", { "prefer": { "returns": "return", "property": "prop" }, "requireReturn": false }], // Not supported in ESLint 9
      "no-unused-vars": "warn",
      "operator-linebreak": ["error", "after", { "overrides": { "?": "ignore", ":": "ignore" } }],
      // Best practices
      "eqeqeq": ["error", "always", { null: "ignore" }],
      "radix": "error",
      "no-alert": "warn",
      "no-console": "warn",
      "no-undef": "error",
      // Add more rules from your plain-language doc as needed
    },
  },
  // Cypress/Node test globals for test files
  {
    files: ["tests/cypress/**/*.js"],
    languageOptions: {
      globals: {
        cy: "readonly",
        Cypress: "readonly",
        assert: "readonly",
        expect: "readonly",
        before: "readonly",
        after: "readonly",
        beforeEach: "readonly",
        afterEach: "readonly",
        it: "readonly",
        describe: "readonly",
        context: "readonly",
      },
    },
    plugins: {
      import: importPlugin,
    },
    rules: {
      "import/no-extraneous-dependencies": "off"
    },
  },
];
