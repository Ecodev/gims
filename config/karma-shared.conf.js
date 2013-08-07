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
        }
    });
};
