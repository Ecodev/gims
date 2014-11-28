module.exports = function(config) {
    config.set({
        frameworks: ['jasmine'],
        autoWatch: true,
        basePath: '../',
        urlRoot: '/__karma/',
        logLevel: config.LOG_INFO,
        logColors: true,
        browsers: ['Chrome'],
        proxies: {
            '/': 'http://gims.lan/'
        },
        // Our custom browser 'cli' will use PhantomJS, but with a huge
        // window size to let ui-grid render as many columns as possible
        customLaunchers: {
            'cli': {
                base: 'PhantomJS',
                options: {
                    viewportSize: {width: 1900, height: 1200}
                }
            }
        },
        files: [
            'htdocs/lib/autoload/*',
            'htdocs/lib/angular-mocks/angular-mocks.js',
            'htdocs/js/**/*.js',
            'tests/unit/**/*.js'
        ],
        junitReporter: {
            outputFile: 'data/logs/karma-unit.xml',
            suite: 'unit'
        }
    }
    );
};
