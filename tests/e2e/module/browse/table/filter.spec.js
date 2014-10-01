/**
 * End2End tests for browse table module
 */

describe('browse/table/filter', function() {

    beforeEach(function() {
        browser.get('/browse/table/filter');
    });

    it('should not render filter table without selection', function() {
        expect(element.all(by.css('[ng-view] .table.table-bordered.table-condensed.table-filter')).count()).toBe(0);
    });

    it('should render select for questionnaires selection', function() {
        expect(element.all(by.css('[ng-view] .select2-container')).count()).toBe(3);
    });

});

describe('browse/table/filter menu', function() {

    beforeEach(function() {
        // Always close menu if it was opened, by clicking anywhere
        element(by.css('footer')).click();
    });

    browser.get('/browse/table/filter?questionnaires=2&filters=3,75,27,9,10');

    it('should render filter table', function() {
        expect(element.all(by.css('[ng-view] .table-filter')).count()).toBe(1);
    });

    it('should have every single type of values', function() {
        expect(element.all(by.css('.input-group-btn .fa-question')).count()).toBe(1);
        expect(element.all(by.css('.input-group-btn .fa-gims-rule')).count()).toBe(1);
        expect(element.all(by.css('.input-group-btn .fa-angle-down')).count()).toBe(1);
        expect(element.all(by.css('.input-group-btn .fa-gims-child')).count()).toBe(1);
        expect(element.all(by.css('.input-group-btn .fa-gims-summand')).count()).toBe(1);
    });

    it('menu for question should be complete', function() {
        element(by.css('.input-group-btn .fa-question')).click();
        expect(element(by.css('.gims-dropdown-menu li:nth-child(2)')).getText()).toContain("Manually answered");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(3)')).getText()).toContain("Tap");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(4)')).getText()).toContain("0.243446");
    });

    it('menu for rule should be complete', function() {
        element(by.css('.input-group-btn .fa-gims-rule')).click();
        expect(element(by.css('.gims-dropdown-menu li:nth-child(2)')).getText()).toContain("Computed with rules");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(4)')).getText()).toContain("Rules used in 1st step of computation");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(5) ul li:nth-child(1)')).getText()).toContain("Total improved (CEN91 - Bangladesh, Urban)");
    });

    it('menu for nothing should be complete', function() {
        element(by.css('.input-group-btn .fa-angle-down')).click();
        expect(element(by.css('.gims-dropdown-menu li:nth-child(2)')).getText()).toContain("No value");
    });

    it('menu for children should be complete', function() {
        element(by.css('.input-group-btn .fa-gims-child')).click();
        expect(element(by.css('.gims-dropdown-menu li:nth-child(2)')).getText()).toContain("Computed with children");
    });

    it('menu for summands should be complete', function() {
        element(by.css('.input-group-btn .fa-gims-summand')).click();
        expect(element(by.css('.gims-dropdown-menu li:nth-child(2)')).getText()).toContain("Computed with summands");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(4)')).getText()).toContain("Summands used for computation");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(5)')).getText()).toContain("Protected ground water (all protected wells or springs)");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(6)')).getText()).toContain("Protected ground water (all protected wells or springs)");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(7)')).getText()).toContain("Protected ground water (all protected wells or springs)");
        expect(element(by.css('.gims-dropdown-menu li:nth-child(8)')).getText()).toContain("Protected ground water (all protected wells or springs)");
    });
});
