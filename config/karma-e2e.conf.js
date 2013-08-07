var sharedConfig = require('./karma-shared.conf');

module.exports = function(config) {
    sharedConfig(config);

    config.set({
        frameworks: ['ng-scenario'],
        files: [
            'tests/e2e/**/*.js'
        ],
        junitReporter: {
            outputFile: 'data/logs/karma-e2e.xml',
            suite: 'e2e'
        }
    });
};
