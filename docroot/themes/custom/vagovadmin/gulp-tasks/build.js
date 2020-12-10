/**
 * @file
 * Task: Build task for running frontend build.
 *
 * Also used to compile sass & PL without watching.
 *
 * Usage: gulp build.
 */

// eslint-disable-next-line no-unused-vars
module.exports = (gulp, plugins, options) => {
  // Frontend build.
  gulp.task("build", [
    "js-lint",
    "sass-lint",
    "sass-for-build",
    "patternlab:generate",
  ]);
};
