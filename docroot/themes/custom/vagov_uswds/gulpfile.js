const gulp = require("gulp");
const sass = require("gulp-sass");
const concat = require("gulp-concat");
sass.compiler = require("node-sass");

const task = "css";
const taskImg = "img";

gulp.task("copy-formation-css", () => {
  console.log("copying VA.gov design system to theme dist directory.");
  const stream = gulp
    .src("./node_modules/@department-of-veterans-affairs/formation/dist/*/**")
    .pipe(gulp.dest("dist"));

  return stream;
});
gulp.task(task, gulp.series("copy-formation-css"));

gulp.task("copy-formation-img", () => {
  console.log("copying VA.gov design system images to theme assets directory.");
  const stream = gulp
    .src(
      "./node_modules/@department-of-veterans-affairs/formation/assets/img/**/**"
    )
    .pipe(gulp.dest("assets/img"));

  return stream;
});
gulp.task(taskImg, gulp.series("copy-formation-img"));

gulp.task("sass", () => {
  return gulp
    .src([
      "./components/**/*.scss",
      "./assets/scss/modules/*.scss",
      "./assets/scss/*.scss",
    ])
    .pipe(concat("styles.scss"))
    .pipe(sass().on("error", sass.logError))
    .pipe(gulp.dest("./assets/css/"));
});

gulp.task("watch", () => {
  gulp.watch("./assets/scss/modules/*.scss", gulp.series("sass"));
  gulp.watch("./assets/scss/*.scss", gulp.series("sass"));
  gulp.watch("./components/**/*.scss", gulp.series("sass"));
});
