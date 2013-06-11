/**
 * End2End test for admin questionnaire module
 */
describe('admin/questionnaire/new', function () {

    beforeEach(function () {
        browser().navigateTo('/admin/questionnaire/new');
        loginUser();
    });

    it('should be displayed tabs', function () {
        var panes = new Array('General info');
        for (var index = 0; index < panes.length; index++) {
            var paneText = panes[index];
            expect(element('[ng-view] .nav-tabs li:eq(' + index + ')').text())
                .toMatch(paneText);
        }
    });
});