/**
 * Log in a newly registered user, if not yet logged in
 */

module.exports.logout = function() {

    element(by.css("[href='/user/logout']")).isDisplayed(function(isVisible) {
        if (isVisible === true) {
            element(by.css("[href='/user/logout']")).click();
        }
    });
};

module.exports.login = function(user, pass, browser) {

    element(by.className('loginButton')).isDisplayed().then(function(isVisible) {

        if (isVisible === true) {

            if (!user) {
                user = browser.params.login.username;
            }

            if (!pass) {
                pass = browser.params.login.password;
            }

            element(by.model('login.identity')).isPresent().then(function(inputIsVisible) {
                if (!inputIsVisible) {
                    element(by.className('loginButton')).click();
                }
                element(by.model('login.identity')).sendKeys(user);
                element(by.model('login.credential')).sendKeys(pass);
                element(by.css("[name='loginForm'] [name='submit']")).click();
                expect(element(by.css("a[href='/user/logout']")).isPresent()).toBe(true);

            });
        }

    });

};

module.exports.capture = function(filename, browser) {
    var fs = require('fs');
    browser.takeScreenshot().then(function(png) {
        var dir = 'data/logs/tests/';
        fs.mkdirSync(dir);
        dir = dir + 'captures/';
        fs.mkdirSync(dir);

        var stream = fs.createWriteStream(dir + filename + '.png');
        stream.write(new Buffer(png, 'base64'));
        stream.end();
    });
};
