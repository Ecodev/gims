/**
 * End2End test for admin survey module
 */

var gimsUtility = require('../../utility');

describe('admin/survey', function() {

    beforeEach(function() {
        browser.get('/admin/survey');
        gimsUtility.login(browser);
    });

    it('should display a grid containing surveys', function() {
        expect(element.all(by.css('[ng-view] [ui-grid]')).count()).toBe(1);
    });

    it('should contains a grid with more than 0 element', function() {
        expect(element.all(by.css('[ng-view] [ui-grid] .ui-grid-cell-contents')).count()).toBeGreaterThan(0);
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
        gimsUtility.login(browser);
    });

    var randomCode;

    function fillSurveyForm() {
        // Computes random code
        randomCode = Math.random().toString(36).substr(2, 4);
        element(by.model('survey.code')).sendKeys(randomCode);
        element(by.model('survey.name')).sendKeys('foo');
        element(by.model('survey.isActive')).element(by.css("[value='0']")).click();
        element(by.model('survey.year')).sendKeys('2013');
        element(by.model('survey.comments')).sendKeys('foo bar');
        element(by.model('survey.dateStart')).sendKeys('2013-05-08');
        element(by.model('survey.dateEnd')).sendKeys('2014-05-08');
    }

    it('should have tab "General" visible but *not* tabs "question", "questionnaires" and "users"', function() {
        var panes = [
            {text: 'General', visible: 1},
            {text: 'Questions', visible: 0},
            {text: 'Users', visible: 0},
            {text: 'Questionnaires', visible: 0}
        ];

        panes.forEach(function(pane) {
            expect(element.all(by.xpath("//*[@ng-view]//*[contains(@class, 'nav-tabs')]//li//a[text()='" + pane.text + "']")).count()).toBe(pane.visible);
        });
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
        element(by.css('form > .btn.btn-danger')).click();
        element(by.css(".modal .btn-danger")).click();

        // Should redirect to survey list
        browser.sleep(1);
        // TODO remove the '#3' this is a bug in angular-bootstrap: https://github.com/angular-ui/bootstrap/issues/2155
        expect(browser.getCurrentUrl()).toBe(browser.baseUrl + '/admin/survey#3');

        // The deleted survey shouldn't be in the list anymore
        element(by.css('[ng-view] [ui-grid]')).all(by.xpath('span[text() = "' + randomCode + '"]')).then(function(elements) {
            expect(elements.length).toBe(0);
        });
    });
});

/**
 * End2End test for admin survey module
 */
describe('admin/survey/edit', function() {

    beforeEach(function() {
        browser.get('/admin/survey/edit/1');
        gimsUtility.login(browser);
    });

    it('should have tabs "General", "question", "questionnaires" and "users"', function() {
        var panes = [
            {text: 'General', visible: 1},
            {text: 'Questions', visible: 1},
            {text: 'Questionnaires', visible: 1},
            {text: 'Users', visible: 1}
        ];
        panes.forEach(function(pane) {
            expect(element.all(by.xpath("//*[@ng-view]//*[contains(@class, 'nav-tabs')]//li//a[text()='" + pane.text + "']")).count()).toBe(pane.visible);
        });
    });
});

/**
 * End2End test for admin survey module
 */
describe('admin/questionnaire/new', function() {

    beforeEach(function() {
        browser.get('/admin/questionnaire/new?survey=23');
        gimsUtility.login(browser);
    });

    function selectFirstItemUiSelect() {
        element(by.css('.ui-select-container')).click();
        element(by.css('.ui-select-choices-row-inner:first-child')).click();
    }

    var selectedCountry;
    function fillQuestionnaireForm() {
        selectFirstItemUiSelect();
        element(by.model('questionnaire.dateObservationStart')).sendKeys('2013-05-08');
        element(by.model('questionnaire.dateObservationEnd')).sendKeys('2014-05-08');
        element(by.model('questionnaire.comments')).sendKeys('foo bar');
    }

    it('should have tab "General" visible but *not* tab "users"', function() {
        var panes = [
            {text: 'General', visible: 1},
            {text: 'Questionnaires', visible: 0}
        ];

        panes.forEach(function(pane) {
            expect(element.all(by.xpath("//*[@ng-view]//*[contains(@class, 'nav-tabs')]//li//a[text()='" + pane.text + "']")).count()).toBe(pane.visible);
        });
    });

    it('should be able to fill-in required fields', function() {
        expect(element.all(by.css('[ng-view] .btn-save[disabled]')).count()).toBe(1);
        expect(element.all(by.css('[ng-view] .btn-saving')).count()).toBe(0);

        fillQuestionnaireForm();
        expect(element.all(by.css('[ng-view] .btn-save[disabled]')).count()).toBe(0);
    });

    it('should redirect to admin/survey/edit after question created', function() {

        fillQuestionnaireForm();

        // Click save button
        element(by.css('[ng-view] .btn-save')).click();

        // Check if the element was found in survey list
        expect(browser.getCurrentUrl()).toContain(browser.baseUrl + '/admin/questionnaire/edit');
    });

    it('should be able to save a new questionnaire and delete it', function() {

        fillQuestionnaireForm();

        // Click save button
        element(by.css('[ng-view] .btn-save')).click();

        // Should redirect to edit URL
        expect(browser.getCurrentUrl()).toMatch(/\/admin\/questionnaire\/edit\/\d+/);

        // Should find the same value that we entered (reloaded from DB)
        expect(element(by.model('questionnaire.comments')).getAttribute('value')).toMatch('foo bar');

        // Delete the questionnaire
        element(by.css('form > .btn.btn-danger')).click();
        element(by.css('.modal .btn-danger')).click();

        // Should redirect to questionnaire list
        expect(browser.getCurrentUrl()).toContain(browser.baseUrl + '/admin/survey/edit/23');

        element(by.xpath("//*[@ng-view]//*[contains(@class, 'nav-tabs')]//li//a[text()='Questionnaires']")).click();

        // The deleted questionnaire shouldn't be in the list anymore
        element(by.css('[gims-grid-questionnaire] [ui-grid]')).all(by.xpath('span[text() = "' + selectedCountry + '"]')).then(function(elements) {
            expect(elements.length).toBe(0);
        });
    });
});

//@todo test create new questionnaire
//@todo test create new question
//@todo test create new users
