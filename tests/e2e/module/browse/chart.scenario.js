/**
 * End2End tests for browse chart module
 */
describe('browse/chart', function () {

    beforeEach(function () {
        browser().navigateTo('/browse/chart');
    });

    it('should render a chart', function () {
        expect(element('[ng-view] [type="area"]').count()).toBe(1);
    });

    it('should render select for geoname, filterSets and parts', function () {
        expect(element('[ng-view] .select2-container').count()).toBe(3);
    });

});