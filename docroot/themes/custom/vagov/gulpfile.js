'use strict';
/*jshint esversion: 6 */

const { src, dest, watch, series } = require('gulp');
const livereload = require('gulp-livereload');
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
const concat = require('gulp-concat');
const sourcemaps = require('gulp-sourcemaps');
const { spawn } = require('child_process');

/**
 * Clear all drupal caches
 */
function clearcache (done) {
    const cr = spawn(
        'lando', ['drush', 'cache-rebuild'],
        { stdio: 'inherit' });
    cr.on('close', done);
}

exports.clearcache = clearcache;

/**
 * @function compileStyles
 * Compile and compress files from scss, add browser prefixes, create a source map, and save in assets folder.
 */
function compileStyles() {
    return src('assets/scss/**/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
        .pipe(autoprefixer('last 2 version'))
        .pipe(sourcemaps.write('./'))
        .pipe(dest('assets/css/'));
}

exports.styles = compileStyles;

/**
 * @function compileScripts
 * Compile files from js, concatenate, create a source map, and save in assets folder.
 */
function compileScripts() {
    return src('assets/js/src/**/*.js')
        .pipe(sourcemaps.init())
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(concat('script.min.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(dest('assets/js'));
}

exports.scripts = compileScripts;

/**
 * @task watch
 * Watch scss, JS, and twig files for changes & recompile
 * Reload browser with livereload to show changes
 */
function  watchFiles() {
    livereload.listen();
    watch('assets/scss/**/*.scss', compileStyles);
    watch('assets/js/src/**/*.js', compileScripts);
    watch(['assets/css/uswds.css', './**/*.html.twig', 'assets/js/*.js'], function (files) {
        livereload.changed(files);
    });

}

exports.watch = watchFiles;

/**
 * @task default
 * running just `gulp` will
 * compile & autoprefix Sass & concatenate JS files, launch livereload, watch files.
 */
exports.default = series(compileStyles, compileScripts, watchFiles);