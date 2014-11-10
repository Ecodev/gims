/**
 * End2End tests for admin module
 */

var gimsUtility = require('../utility');

describe('admin', function() {

    beforeEach(function() {
        browser.get('/admin');
        gimsUtility.login(browser);
    });

    it('should render admin', function() {
        expect(element(by.css('.jumbotron p')).getText()).toContain("Manage Surveys, Users, Filters and Rules");
    });

    it('should render admin buttons', function() {
        var text = element(by.css('[ng-view] .col-md-12')).getText();
        expect(text).toContain('Surveys');
        expect(text).toContain('Users');
        expect(text).toContain('Filter sets');
        expect(text).toContain('Filters');
        expect(text).toContain('Rules');
    });
});
