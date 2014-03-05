/**
 * End2End tests for about module
 */
describe('about', function() {

    beforeEach(function() {
        browser().navigateTo('/about');
    });

    it('should render about when user navigates to /about', function() {
        expect(element('[ng-view] p:first').text()).
                toMatch(/Learn where the project stems from and what are its goals/);
    });

});