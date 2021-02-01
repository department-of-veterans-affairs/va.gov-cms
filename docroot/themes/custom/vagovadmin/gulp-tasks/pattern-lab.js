/**
 * @file
 * Task: Generate PatternLab, Watch PatternLab.
 */

module.exports = (gulp, options, plugins) => {
  // Start the Patternlab server and watch for changes.
  gulp.task("patternlab:watch", () => {
    // eslint-disable-next-line no-shadow
    const options = {
      continueOnError: false, // Default = false, true means don't emit error event.
      pipeStdout: false, // Default = false, true means stdout is written to file.contents.
    };
    const reportOptions = {
      err: true, // Default = true, false means don't write err.
      stderr: true, // Default = true, false means don't write stderr.
      stdout: true, // Default = true, false means don't write stdout.
    };
    // eslint-disable-next-line no-console
    console.log(
      "server started on http://localhost:8080 - use ctrl+c to exit..."
    );
    return gulp
      .src("./pattern-lab")
      .pipe(
        plugins.exec(
          "cd <%= file.path %> && php core/console --server --with-watch",
          options
        )
      )
      .pipe(plugins.exec.reporter(reportOptions));
  });

  // Export Pattern lab.
  gulp.task("patternlab:generate", () => {
    // eslint-disable-next-line no-shadow
    const options = {
      continueOnError: false, // Default = false, true means don't emit error event.
      pipeStdout: false, // Default = false, true means stdout is written to file.contents.
    };
    const reportOptions = {
      err: true, // Default = true, false means don't write err.
      stderr: true, // Default = true, false means don't write stderr.
      stdout: true, // Default = true, false means don't write stdout.
    };
    return gulp
      .src("./pattern-lab")
      .pipe(
        plugins.exec(
          "cd <%= file.path %> && php core/console --generate",
          options
        )
      )
      .pipe(plugins.exec.reporter(reportOptions));
  });
};
