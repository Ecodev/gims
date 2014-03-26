/**
 * End2End tests for home module
 */
describe('home', function() {

    beforeEach(function() {
        browser.get('/home');
    });

    it('should render home when user navigates to /home', function() {
        expect(element(by.css('[ng-view] p:nth-of-type(1)')).getText()).toContain("Global Information Management System");
    });
});
