/**
 * @file
 * Task: Gulp.
 *
 * Default gulp task.
 */

module.exports = function (gulp, plugins, options) {
  // Watch.
  gulp.task("default", ["sass-lint", "sass", "patternlab:watch", "watch"]);
};
