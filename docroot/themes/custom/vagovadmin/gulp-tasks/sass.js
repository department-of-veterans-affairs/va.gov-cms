/**
 * @file
 * Task: Compile: Sass.
 */

module.exports = function (gulp, options, plugins) {
  'use strict';

  gulp.task('sass', function () {
    return gulp.src([
        options.sass.sassFiles,
        options.sass.plFiles
      ])

      .pipe(plugins.sourcemaps.init()) // Sourcemaps can turned off for prod. Comment this line and the sourcemaps line below if needed.
      .pipe(plugins.sassglob())
      .pipe(plugins.sass({
        outputStyle: 'compact', // Compressed is the best option here but needs sourcemaps turned off for it to work.
        includePaths: [
          'node_modules/breakpoint-sass/stylesheets',
          'node_modules/bourbon-neat/core'
        ]
      }).on('error', plugins.sass.logError))
      .pipe(plugins.prefix({
        browsers: ['last 2 versions'],
        cascade: false
      }))
      .pipe(plugins.postcss([
        plugins.mqpacker({ sort: true})
      ]))
      .pipe(plugins.concat('styles.css'))
      .pipe(plugins.sourcemaps.write()) // Comment this too to remove sourcemaps.
      .pipe(gulp.dest(options.css.cssFiles));
  });

};
