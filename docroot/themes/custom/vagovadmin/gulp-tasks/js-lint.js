/**
 * @file
 * Task: Lint JS.
 */

module.exports = (gulp, options, plugins) => {
  // Set up JS lint.
  gulp.task("js-lint", () => {
    return (
      gulp
        .src([options.js.jsFiles, "!node_modules/**"])
        // eslint() attaches the lint output to the "eslint" property
        // of the file object so it can be used by other modules.
        .pipe(plugins.eslint())
        .pipe(plugins.eslint.format())
        .pipe(plugins.eslint.failAfterError())
    );
  });
};
