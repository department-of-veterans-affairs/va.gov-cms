'use strict';

var gulp = require('gulp'),
    livereload = require('gulp-livereload'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    sourcemaps = require('gulp-sourcemaps'),
    concat = require('gulp-concat'),
    cp = require('child_process');

/**
 * @task sass
 * Compile and compress files from scss, add browser prefixes, create a source map, and save in assets folder.
 */
gulp.task('sass', function () {
  gulp.src('assets/scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(autoprefixer('last 2 version'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('assets/css/'));
});

/**
 * @task scripts
 * Compile files from js
 */
gulp.task('scripts', function() {
    return gulp.src(['assets/js/src/**/*.js'])
        .pipe(sourcemaps.init())
        .pipe(concat('scripts.js'))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('assets/js/dist/'));
});

/**
 * @task clearcache
 * Clear all drupal caches
 */
gulp.task('clearcache', function(done) {
    return cp.spawn('lando', ['drush'], ['cache-rebuild'], {stdio: 'inherit'})
        .on('close', done);
});

/**
 * @task watch
 * Watch scss, JS, and twig files for changes & recompile
 * Clear cache when Drupal related files are changed
 */
gulp.task('watch', function () {
  livereload.listen();

  gulp.watch('assets/scss/**/*.scss', ['sass']);
  gulp.watch('assets/js/src/**/*.js', ['scripts']);
  gulp.watch(['assets/css/uswds.css', './**/*.html.twig', 'assets/js/dist/*.js'], function (files) {
    livereload.changed(files);
  });
});

/**
 * Default task, running just `gulp` will
 * compile Sass & JS files, launch livereload, watch files.
 */
gulp.task('default', ['sass', 'scripts', 'watch']);
