/**
 * End2End tests for about module
 */

describe('about', function() {

    beforeEach(function() {
        browser.get('/about');
    });

    it('should render about', function() {
        expect(browser.getCurrentUrl()).toEqual(browser.baseUrl + '/about');
        expect(element(by.css('.jumbotron p')).getText()).toEqual('Learn where the project stems from and what are its goals');
    });

});
