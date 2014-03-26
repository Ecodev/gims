/**
 * End2End tests for contribute module
 */
var gimsUtility = require('../utility');

describe('contribute', function() {

    beforeEach(function() {
        browser.driver.manage().window().setSize(1280, 1024);
        browser.get('/contribute');
        gimsUtility.login(undefined, undefined, browser);
    });

    it('should render contribute when user navigates to /contribute', function() {
        expect(element(by.css('[ng-view] p:nth-of-type(1)')).getText()).toContain("Small streams make large rivers");
    });

    it('should render admin buttons', function() {
        gimsUtility.capture('test ', browser);
        expect(element.all(by.css('[ng-view] .col-md-4')).count()).toBe(3);

        var text = element(by.css('[ng-view] .col-md-4:nth-child(1)')).getText();
        expect(text).toContain('Surveys');
        expect(text).toContain('Users');
        expect(text).toContain('Filter sets');
        expect(text).toContain('Filters');
    });

    it('should render other buttons', function() {
        var text = element(by.css('[ng-view] .col-md-4:nth-child(2)')).getText();
        expect(text).toContain("Questionnaires");
    });
});
