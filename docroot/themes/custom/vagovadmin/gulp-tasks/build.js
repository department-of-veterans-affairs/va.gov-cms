/**
 * @file
 * Task: Build task for running frontend build.
 *
 * Also used to compile sass & PL without watching.
 *
 * Usage: gulp build.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  // Frontend build.
  gulp.task('build', [
    'js-lint',
    'sass-lint',
    'sass-for-build',
    'patternlab:generate'
  ]);

};
