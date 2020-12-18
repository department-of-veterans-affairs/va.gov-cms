/**
 * @file
 * Task: Gulp.
 *
 * Default gulp task.
 */

// eslint-disable-next-line no-unused-vars
module.exports = (gulp, plugins, options) => {
  // Watch.
  gulp.task("default", ["sass-lint", "sass", "patternlab:watch", "watch"]);
};
