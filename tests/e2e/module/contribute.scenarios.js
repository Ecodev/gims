/**
 * End2End tests for contribute module
 */
describe('contribute', function () {

    beforeEach(function () {
        browser().navigateTo('/contribute');
    });

    it('should render contribute when user navigates to /contribute', function () {
        expect(element('[ng-view] p:first').text()).
            toMatch(/Small streams make large rivers/);
    });

    it('should render admin buttons', function () {

        expect(element('[ng-view] .span4:nth-child(1) li:nth-child(1) p').text()).
            toMatch(/Surveys/);

        expect(element('[ng-view] .span4:nth-child(1) li:nth-child(2) p').text()).
            toMatch(/Users/);
    });

    it('should render other buttons', function () {
        expect(element('[ng-view] .span4:nth-child(2) li:nth-child(1) p').text()).
            toMatch(/Questionnaires/);
    });
});