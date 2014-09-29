/**
 * End2End test for admin questionnaire module
 */
var gimsUtility = require('./../../utility');

describe('admin/questionnaire/new', function() {

    beforeEach(function() {
        browser.get('/admin/questionnaire/new');
        //browser.get('/home');
        gimsUtility.login(undefined, undefined, browser);
    });

    it('should be displayed tabs', function() {
        var panes = new Array('General');
        expect(element.all(by.css('[ng-view] .nav-tabs li')).count()).toEqual(panes.length);
        for (var index = 0; index < panes.length; index++) {
            var paneText = panes[index];
            expect(element.all(by.css('[ng-view] .nav-tabs li')).get(index).getText()).toEqual(paneText);
        }
    });
});
