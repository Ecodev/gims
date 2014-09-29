/**
 * End2End tests for home module
 */
describe('home', function() {

    beforeEach(function() {
        browser.get('/home');
    });

    it('should render home', function() {
        expect(element(by.css('.jumbotron p')).getText()).toContain("Global Information Management System");
    });
});
