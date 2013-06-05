/**
 * End2End tests for admin module
 */
describe('admin', function () {

    beforeEach(function () {
        browser().navigateTo('/admin');
        loginUser();
    });

    it('should redirect to /contribute when user navigates to /admin', function () {
        expect(browser().location().path()).toMatch('/contribute');
    });
});