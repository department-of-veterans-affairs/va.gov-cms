/**
 * @file
 */

/* jshint esversion: 6 */

const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
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
    .pipe(gulp.dest("dist/"));
});
gulp.task("sass").description =
  "process SCSS files: compile to compressed css, add browser prefixes, create a source map, and save in assets folder";

/**
 * @task clearcache
 * Clear all drupal caches
 */
gulp.task("clearcache", (done) => {
  const child = spawn("lando drush cache-rebuild", {
    stdio: "inherit",
    shell: "true",
  });
  child.on("close", done);
});
gulp.task("clearcache").description = "clear all Drupal caches";

/**
 * @task watch
 * Watch scss files for changes & recompile, clear drupal caches.
 * Refresh browser to show changes (no hot reload)
 */
gulp.task("watch", () => {
  // usePolling necessary for file changes mounted through docker
  const options = {
    ignoreInitial: false,
    usePolling: true,
  };

  gulp.watch(
    ["assets/scss/**/*.scss"],
    options,
    gulp.series("sass", "clearcache")
  );
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
