/**
 * End2End tests for browse table module
 */

describe('browse/table', function() {

    beforeEach(function() {
        browser.get('/browse/table/filter');
    });

    it('should render a grid', function() {
        expect(element.all(by.css('[ng-view] .table.table-bordered.table-condensed.bigtable')).count()).toBe(1);
    });

    it('should render select for survey selectinon and filter selection', function() {
        expect(element.all(by.css('[ng-view] .select2-container')).count()).toBe(7);
    });

});
