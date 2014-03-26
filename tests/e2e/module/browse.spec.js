/**
 * End2End tests for browse module
 */
describe('browse', function() {

    beforeEach(function() {
        browser.get('/browse');
    });

    it('should render about when user navigates to /browse', function() {
        expect(element(by.css('[ng-view] .browse .col-md-4:nth-child(1)')).getText()).toContain("Maps");
        expect(element(by.css('[ng-view] .browse .col-md-4:nth-child(2)')).getText()).toContain("Charts");

        var text = element(by.css('[ng-view] .browse .col-md-4:nth-child(3)')).getText();
        expect(text).toContain("Filters");
        expect(text).toContain("Questionnaires");
        expect(text).toContain("Countries");
    });

});
