var gulp = require('gulp'),
  livereload = require('gulp-livereload'),
  sass = require('gulp-sass'),
  autoprefixer = require('gulp-autoprefixer'),
  sourcemaps = require('gulp-sourcemaps');

gulp.task('sass', function () {
  gulp.src('assets/scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(autoprefixer('last 2 version'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('assets/css/'));
});

gulp.task('watch', function () {
  livereload.listen();

  gulp.watch('assets/scss/**/*.scss', ['sass']);
  gulp.watch(['assets/css/uswds.css', './**/*.html.twig', './js/*.js'], function (files) {
    livereload.changed(files)
  });
});

gulp.task('default', ['sass', 'watch']);
