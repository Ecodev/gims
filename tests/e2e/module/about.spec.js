/**
 * End2End tests for about module
 */

describe('about', function() {

    beforeEach(function() {
        browser.get('/about');
    });

    it('should render about when user navigates to /about', function() {
        expect(browser.getCurrentUrl()).toEqual(browser.baseUrl+'/about');
        expect(element(by.css('[ng-view] p:nth-of-type(1)')).getText()).toEqual('Learn where the project stems from and what are its goals');
    });

});


