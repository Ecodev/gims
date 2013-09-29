/**
 * End2End tests for browse table module
 */
describe('browse/table', function () {

    beforeEach(function () {
        browser().navigateTo('/browse/table/filter');
    });

    it('should render a grid', function () {
        expect(element('[ng-view] .ngViewport').count()).toBe(1);
    });

    it('should render select for questionnaires and filterSets', function () {
        expect(element('[ng-view] .select2-container').count()).toBe(2);
    });

});