/**
 * @file
 * Task: Generate PatternLab, Watch PatternLab.
 */

module.exports = function (gulp, options, plugins) {
  'use strict';

  // Start the Patternlab server and watch for changes.
  gulp.task('patternlab:watch', function () {
    var options = {
      continueOnError: false, // Default = false, true means don't emit error event.
      pipeStdout: false // Default = false, true means stdout is written to file.contents.
    };
    var reportOptions = {
      err: true, // Default = true, false means don't write err.
      stderr: true, // Default = true, false means don't write stderr.
      stdout: true // Default = true, false means don't write stdout.
    };
    console.log('server started on http://localhost:8080 - use ctrl+c to exit...');
    return gulp.src('./pattern-lab')
      .pipe(plugins.exec('cd <%= file.path %> && php core/console --server --with-watch', options))
      .pipe(plugins.exec.reporter(reportOptions));
  });

  // Export Pattern lab.
  gulp.task('patternlab:generate', function () {
    var options = {
      continueOnError: false, // Default = false, true means don't emit error event.
      pipeStdout: false // Default = false, true means stdout is written to file.contents.
    };
    var reportOptions = {
      err: true, // Default = true, false means don't write err.
      stderr: true, // Default = true, false means don't write stderr.
      stdout: true // Default = true, false means don't write stdout.
    };
    return gulp.src('./pattern-lab')
      .pipe(plugins.exec('cd <%= file.path %> && php core/console --generate', options))
      .pipe(plugins.exec.reporter(reportOptions));
  });
};
