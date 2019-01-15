gemini.suite('logo', (suite) => {
    suite.setUrl('/')
        .setCaptureElements('.usa-logo')
        .capture('plain');
});
