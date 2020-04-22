/**
 * @file
 * Task: Lint & Watch Sass.
 *
 * Usage: gulp watch.
 */

module.exports = function (gulp, options, plugins) {
  'use strict';

  function lintFile(file) {
    gulp.src(file)
      .pipe(plugins.sassLint())
      .pipe(plugins.sassLint.format());
  }

  // Keep an eye on Sass files for changes and only lint changed files.
  gulp.task('watch', function () {
    gulp.watch([
      options.sass.sassFiles,
      options.sass.plFiles
    ], function (ev) {
      if (ev.type === 'added' || ev.type === 'changed') {
        lintFile(ev.path);
      }
    });
    // Compile sass changes.
    gulp.watch([
        options.sass.sassFiles,
        options.sass.plFiles],
      ['sass']);
  });
};
