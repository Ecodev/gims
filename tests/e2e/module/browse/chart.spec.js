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

    it('should render select for geoname, filter, filterSets, parts and projection', function() {
        expect(element.all(by.css('[ng-view] .ui-select-container')).count()).toBe(4);
    });

});
