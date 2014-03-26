/**
 * End2End test for admin survey module
 */

var gimsUtility = require('../../utility');

describe('admin/survey', function() {

    beforeEach(function() {
        browser.get('/admin/survey');
        gimsUtility.login(undefined, undefined, browser);
    });

    it('should display a grid containing surveys', function() {
        expect(element.all(by.css('[ng-view] [ng-grid]')).count()).toBe(1);
    });

    it('should contains a grid with more than 0 element', function() {
        expect(element.all(by.css('[ng-view] [ng-grid] .ngCanvas')).count()).toBeGreaterThan(0);
    });

    it('should contains a link for creating new survey', function() {
        expect(element.all(by.css('[ng-view] .link-new')).count()).toBe(1);
    });

    it('should lead to /admin/survey/new when following link new survey', function() {
        element(by.css('[ng-view] .link-new')).click();
        expect(browser.getCurrentUrl()).toMatch(browser.baseUrl + '/admin/survey/new');
    });
});

/**
 * End2End test for admin survey module
 */
describe('admin/survey/new', function() {

    beforeEach(function() {
        browser.get('/admin/survey/new');
        gimsUtility.login(undefined, undefined, browser);
    });

    var randomCode;

    function fillSurveyForm() {
        // Computes random code
        randomCode = Math.random().toString(36).substr(2, 4);
        element(by.model('survey.code')).sendKeys(randomCode);
        element(by.model('survey.name')).sendKeys('foo');
        element(by.model('survey.isActive')).findElement(by.css("[value='0']")).click();
        element(by.model('survey.year')).sendKeys('2013');
        element(by.model('survey.comments')).sendKeys('foo bar');
        element(by.model('survey.dateStart')).sendKeys('08/05/2013');
        element(by.model('survey.dateEnd')).sendKeys('08/05/2014');
    }

    it('should have tab "General info" visible but *not* tabs "question", "questionnaires" and "users"', function() {
        var panes = [
            {text: 'General info', visible: 1},
            {text: 'Questions', visible: 0},
            {text: 'Users', visible: 0},
            {text: 'Questionnaires', visible: 0}
        ];

        panes.forEach(function(pane){
            element(by.css('[ng-view] .nav-tabs li'))
                .findElements(by.xpath("//a[text()='" + pane.text + "']"))
                .then(function(elements){
                    expect(elements.length).toBe(pane.visible);
                });
        })
    });

    it('should be able to fill-in required fields', function() {
        expect(element.all(by.css('[ng-view] .btn-save[disabled]')).count()).toBe(1);
        expect(element.all(by.css('[ng-view] .btn-saving')).count()).toBe(0);

        fillSurveyForm();
        expect(element.all(by.css('[ng-view] .btn-save[disabled]')).count()).toBe(0);
    });

    it('should redirect to admin/survey/edit after survey created', function() {

        fillSurveyForm();

        // Click save button
        element(by.css('[ng-view] .btn-save')).click();

        // Check if the element was found in survey list
        expect(browser.getCurrentUrl()).toContain(browser.baseUrl + '/admin/survey/edit');
    });

    it('should be able to save a new survey and delete it', function() {

        fillSurveyForm();

        // Click save button
        element(by.css('[ng-view] .btn-save')).click();

        // Should redirect to edit URL
        browser.sleep(1);
        expect(browser.getCurrentUrl()).toMatch(/\/admin\/survey\/edit\/\d+/);

        // Should find the same value that we entered (reloaded from DB)
        expect(element(by.model('survey.code')).getAttribute('value')).toMatch(randomCode);

        // Delete the survey
        element(by.css('form .btn.btn-danger')).click();
        element(by.className("modal")).findElement(by.className('btn-danger')).click();

        // Should redirect to survey list
        browser.sleep(1);
        expect(browser.getCurrentUrl()).toBe(browser.baseUrl + '/admin/survey');

        // The deleted survey shouldn't be in the list anymore
        element(by.css('[ng-view] [ng-grid]')).findElements(by.xpath('span[text() = "' + randomCode + '"]')).then(function(elements){
            expect(elements.length).toBe(0);
        });
    });
});

///**
// * End2End test for admin survey module
// */
//describe('admin/survey/edit', function () {
//
//    beforeEach(function () {
//        //@todo
//        browser.get('/admin/survey/edit/1');
//        gimsUtility.login(undefined, undefined, browser);
//    });
//
//    it('should have tabs "General info", "question", "questionnaires" and "users"', function () {
//        var panes = new Array(
//            {text: 'General info', visible: 1},
//            {text: 'Questions', visible: 1},
//            {text: 'Questionnaires', visible: 1},
//            {text: 'Users', visible: 1}
//        );
//        for (var index = 0; index < panes.length; index++) {
//            var pane = panes[index];
//            expect(element('[ng-view] .nav-tabs li a:contains("' + pane.text + '")').count())
//                .toBe(pane.visible);
//        }
//    });
// @todo test create new questionnaire
// @todo test create new question
// @todo test create new users
//});
