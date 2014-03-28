/**
 * End2End tests for browse chart module
 */
describe('browse/chart', function() {

    beforeEach(function() {
        browser.get('/browse/chart');
    });

    it('should render a chart', function() {
        expect(element.all(by.css('[ng-view] [type="area"]')).count()).toBe(1);
    });

    it('should render select for geoname, filterSets and parts', function() {
        expect(element.all(by.css('[ng-view] .select2-container')).count()).toBe(3);
    });

});
