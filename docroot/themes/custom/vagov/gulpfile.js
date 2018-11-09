'use strict';

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    sourcemaps = require('gulp-sourcemaps'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    cp = require('child_process'),
    babel = require('gulp-babel'),
    browsersync = require('browser-sync').create();


/**
 * @task reload
 * Reload browsersync server
 */
gulp.task('reload', function(done) {
    browsersync.reload();
    done();
});
gulp.task('reload').description = "reload browsersync server: to make it show new changes.";

/**
 * @task serve
 * Start browsersync server - for viewing changes made to CSS, JS, and twig without having to refresh
 */
gulp.task('serve', function(done) {
    browsersync.init({
        proxy: 'vagovcms.lndo.site',
        browserSyncPort: 3001,
        reloadDelay: 2000
    });
    done();
});
gulp.task('serve').description = "start browsersync server: for viewing changes made to CSS, JS, and twig without having to refresh";

/**
 * @task sass
 * Compile and compress files from scss, add browser prefixes, create a source map, and save in assets folder.
 */
gulp.task('sass', function () {
    return gulp.src('assets/scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(autoprefixer('last 2 version'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('assets/css/'));
});
gulp.task('sass').description = "process SCSS files:, compile to compressed css, add browser prefixes, create a source map, and save in assets folder";

/**
 * @task scripts
 * Compile files from js, concatenate, create a source map, and save in assets folder.
 */
gulp.task('scripts', function() {
    return gulp.src(['assets/js/src/**/*.js'])
        .pipe(sourcemaps.init())
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(concat('script.min.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('assets/js'));
});
gulp.task('scripts').description = "process JS files: concatenate, minify, create a source map, and save in assets folder.";

/**
 * @task clearcache
 * Clear all drupal caches
 */
gulp.task('clearcache', function(done) {
    return cp.spawn('lando', ['drush'], ['cache-rebuild'], {stdio: 'inherit'})
        .on('close', done);
});
gulp.task('clearcache').description = "Clear all Drupal caches";

/**
 * @task watch
 * Watch scss, JS, and twig files for changes & recompile
 * Reload browser with livereload to show changes
 */
gulp.task('watch', function () {
  gulp.watch('assets/scss/**/*.scss', gulp.series('sass'));
  gulp.watch('assets/js/src/**/*.js', gulp.series('scripts'));
  gulp.watch(['assets/css/uswds.css', './**/*.html.twig', 'assets/js/*.js'], gulp.series('clearcache', 'reload'));
});
gulp.task('watch').description = "Watch scss, JS, and twig files for changes & reload browser to show changes";

/**
 * Default task, running just `gulp` will
 * compile & autoprefix Sass & concatenate JS files, launch livereload, watch files.
 */
gulp.task('default', gulp.series('sass', 'scripts', 'serve', 'watch'));
gulp.task('default').description = "process scss, process js, launch browser sync, watch twig, js, scss files for changes.";
