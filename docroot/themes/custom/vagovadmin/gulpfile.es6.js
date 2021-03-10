/**
 * @file
 */

/* jshint esversion: 6 */

const gulp = require("gulp");
const sass = require("gulp-sass");
const autoprefixer = require("gulp-autoprefixer");
const sourcemaps = require("gulp-sourcemaps");

const { spawn } = require("child_process");

/**
 * @task sass
 * Compile and compress files from scss, add browser prefixes, create a source map, and save in assets folder.
 */
gulp.task("sass", () => {
  return gulp
    .src(["assets/scss/**/*.scss"])
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: "compressed" }).on("error", sass.logError))
    .pipe(autoprefixer("last 2 version"))
    .pipe(sourcemaps.write("./"))
    .pipe(gulp.dest("assets/css/"));
});
gulp.task("sass").description =
  "process SCSS files: compile to compressed css, add browser prefixes, create a source map, and save in assets folder";

/**
 * @task clearcache
 * Clear all drupal caches
 */
gulp.task("clearcache", (done) => {
  const child = spawn("lando drush cache:rebuild", {
    stdio: "inherit",
    shell: "true",
  });
  child.on("close", done);
});
gulp.task("clearcache").description = "clear all Drupal caches";

/**
 * @task watch
 * Watch scss, JS, and twig files for changes & recompile
 * Reload browser with browsersync to show changes
 */
gulp.task("watch", () => {
  gulp.watch(["scss/**/*.scss"], gulp.series("sass"));
});
gulp.task("watch").description = "watch SCSS";

/**
 * @task default
 * Default task, running just `gulp` will
 * compile & autoprefix Sass.
 */
gulp.task("default", gulp.series("sass"));
gulp.task("default").description =
  "process SCSS, watch SCSS files for changes.";
