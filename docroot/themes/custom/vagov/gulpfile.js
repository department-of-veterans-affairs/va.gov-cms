var gulp = require('gulp'),
    livereload = require('gulp-livereload'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    sourcemaps = require('gulp-sourcemaps'),
    babel = require('gulp-babel'),
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
    return gulp.src(['assets/js/*.js', 'assets/js/custom.js'])
        .pipe(babel({
            presets: ['es2015']
        }))
        .pipe(concat('scripts.js'))
        .pipe(gulp.dest('js'));
});

/**
 * @task clearcache
 * Clear all caches
 */
gulp.task('clearcache', function(done) {
    return cp.spawn('lando drush', ['cache-rebuild'], {stdio: 'inherit'})
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
  gulp.watch(['assets/css/uswds.css', './**/*.html.twig', './js/*.js'], ['clearcache'], function (files) {
    livereload.changed(files);
  });
});

/**
 * Default task, running just `gulp` will
 * compile Sass files, launch BrowserSync, watch files.
 */
gulp.task('default', ['sass', 'scripts', 'watch']);
