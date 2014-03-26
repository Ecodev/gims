/**
 * Log in a newly registered user, if not yet logged in
 */

module.exports.logout = function() {

    element(by.css("[href='/user/logout']")).isDisplayed(function(isVisible){
        if (isVisible == true) {
            element(by.css("[href='/user/logout']")).click();
        }
    });
}

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

            });
        }

    });

}

module.exports.capture = function(filename, browser) {
    var fs = require('fs');
    browser.takeScreenshot().then(function(png) {
        var stream = fs.createWriteStream('data/logs/tests/captures/' + filename + '.png');
        stream.write(new Buffer(png, 'base64'));
        stream.end();
    });
}

