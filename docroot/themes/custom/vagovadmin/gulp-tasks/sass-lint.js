/**
 * @file
 * Task: Lint: Sass.
 */

module.exports = function(gulp, options, plugins) {
  'use strict';
  // Sass linting task
  gulp.task('sass-lint', function() {
    return gulp.src([
        options.sass.sassFiles,
        options.sass.plFiles
      ])

      .pipe(plugins.sassLint({
        rules: {
          // Find sass-lint rules in .sass-lint.yml
        },
        files: {
          //ignore: 'design-library/**/_typey**.scss'
        }
      }))
      .pipe(plugins.sassLint.format())
      .pipe(plugins.sassLint.failOnError())
  });

};
