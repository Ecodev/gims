exports.config = {
    // Spec patterns are relative to the location of this config.
    specs: [
        '../tests/e2e/**/*.spec.js'
    ],
    baseUrl: 'http://gims.lan',
    // The params object will be passed directly to the protractor instance,
    // and can be accessed from your test. It is an arbitrary object and can
    // contain anything you may need in your test.
    // This can be changed via the command line as:
    //   --params.login.user 'Joe'
    params: {
        login: {
            username: 'gims@gims.pro',
            password: 'gimsgims'
        }
    }
};
