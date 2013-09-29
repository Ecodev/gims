/**
 * End2End tests for browse module
 */
describe('browse', function () {

    beforeEach(function () {
        browser().navigateTo('/browse');
    });

    it('should render about when user navigates to /browse', function () {
        expect(element('[ng-view] .browse .span4:nth-child(1) p').text()).
            toMatch(/Maps/);

        expect(element('[ng-view] .browse .span4:nth-child(2) p').text()).
            toMatch(/Charts/);

        expect(element('[ng-view] .browse .span4:nth-child(3) p').text()).
            toMatch(/FiltersQuestionnaires/);
    });

});