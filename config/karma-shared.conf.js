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
            '/': 'http://gims.local/'
        },
        // Our custom browser 'cli' will use PhantomJS, but with a huge
        // window size to let ng-grid render as many columns as possible
        customLaunchers: {
            'cli': {
                base: 'PhantomJS',
                options: {
                    viewportSize: {width: 1900, height: 1200}
                }
            }
        }
    });
};
