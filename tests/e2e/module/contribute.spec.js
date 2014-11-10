/**
 * End2End tests for contribute module
 */
var gimsUtility = require('../utility');

describe('contribute', function() {

    beforeEach(function() {
        browser.driver.manage().window().setSize(1280, 1024);
        browser.get('/contribute');
        gimsUtility.login(browser);
    });

    it('should render contribute', function() {
        expect(element(by.css('.jumbotron p')).getText()).toContain("Small streams make large rivers");
    });

    it('should render contribute buttons', function() {
        var text = element(by.css('[ng-view] .col-sm-6:first-child')).getText();
        expect(text).toContain('JMP questionnaires');
        expect(text).toContain('NSA questionnaires');
        expect(text).toContain('GLAAS questionnaires');
    });
});
