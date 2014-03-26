/**
 * End2End tests for admin module
 */

var gimsUtility = require('../utility');

describe('admin', function() {

    beforeEach(function() {
        browser.get('/admin');
        gimsUtility.login(undefined, undefined, browser);
    });

    it('should redirect to /contribute when user navigates to /admin', function() {
        expect(browser.getCurrentUrl()).toEqual(browser.baseUrl+'/contribute');
    });
});
