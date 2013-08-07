var sharedConfig = require('./karma-shared.conf');

module.exports = function(config) {
    sharedConfig(config);

    config.set({
        files: [
            'htdocs/lib/autoload/*',
            'htdocs/lib/angular/angular-mocks.js',
            'htdocs/js/**/*.js',
            'tests/unit/**/*.js'
        ],
        junitReporter: {
            outputFile: 'data/logs/karma-unit.xml',
            suite: 'unit'
        }
    });
};
