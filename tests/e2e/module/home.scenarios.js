/**
 * End2End tests for home module
 */
describe('home', function () {

    beforeEach(function () {
        browser().navigateTo('/home');
    });

    it('should render home when user navigates to /home', function () {
        expect(element('[ng-view] p:first').text()).
            toMatch(/Global Information Management System/);
    });
});