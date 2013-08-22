/**
 * End2End test for admin survey module
 */
describe('admin/survey', function() {

    beforeEach(function() {
        browser().navigateTo('/admin/survey');
        loginUser();
    });

    it('should display a grid containing surveys', function() {
        expect(element('[ng-view] [ng-grid]').count()).toBe(1);
    });

    it('should contains a grid with more than 0 element', function() {
        expect(element('[ng-view] [ng-grid] .ngCanvas').count()).toBeGreaterThan(0);
    });

    it('should contains a link for creating new survey', function() {
        expect(element('[ng-view] .link-new').count()).toBe(1);
    });

    it('should lead to /admin/survey/new when following link new survey', function() {
        element('[ng-view] .link-new').click();
        expect(browser().location().path()).toMatch('/admin/survey/new');
    });
});

/**
 * End2End test for admin survey module
 */
describe('admin/survey/new', function() {

    beforeEach(function() {
        browser().navigateTo('/admin/survey/new');
        loginUser();
    });

    var randomCode;

    function fillSurveyForm() {

        // Computes random code
        randomCode = Math.random().toString(36).substr(2, 4);

        input('survey.code').enter(randomCode);
        input('survey.name').enter('foo');
        select('survey.isActive').option(0);
        input('survey.year').enter('2013');
        input('survey.comments').enter('foo bar');
        input('survey.dateStart').enter('08/05/2013');
        input('survey.dateEnd').enter('08/05/2014');
    }

    it('should have tab "General info" visible but *not* tabs "question", "questionnaires" and "users"', function() {
        var panes = new Array(
                {text: 'General info', visible: 1},
        {text: 'Questions', visible: 0},
        {text: 'Questionnaires', visible: 0},
        {text: 'Users', visible: 0}
        );
        for (var index = 0; index < panes.length; index++) {
            var pane = panes[index];
            expect(element('[ng-view] .nav-tabs li a:contains("' + pane.text + '")').count())
                    .toBe(pane.visible);
        }
    });

    it('should be able to fill-in required fields', function() {
        expect(element('[ng-view] .btn-save[disabled]').count())
                .toBe(1);

        expect(element('[ng-view] .btn-saving').count())
                .toBe(0);

        fillSurveyForm();

        expect(element('[ng-view] .btn-save[disabled]').count())
                .toBe(0);
    });

    it('should redirect to admin/survey/edit after survey created', function() {

        fillSurveyForm();

        // Click save button
        element('[ng-view] .btn-save').click();

        // Check if the element was found in survey list
        expect(browser().window().path()).toContain('admin/survey/edit');
    });

    it('should be able to save a new survey and delete it', function() {

        fillSurveyForm();

        // Click save button
        element('[ng-view] .btn-save').click();

        // Should redirect to edit URL
        sleep(1);
        expect(browser().location().path()).toMatch(/\/admin\/survey\/edit\/\d+/);

        // Should find the same value that we entered (reloaded from DB)
        expect(input('survey.code').val()).toMatch(randomCode);

        // Delete the survey
        element('form .btn.btn-danger').click();
        element('.modal:has(p:contains("' + randomCode + '")) .btn-danger').click();

        // Should redirect to survey list
        sleep(1);
        expect(browser().location().path()).toBe('/admin/survey');

        // The deleted survey should be in the list anymore
        expect(element('[ng-view] [ng-grid] .ngRow:has(span:contains("' + randomCode + '"))').count()).toBe(0);
    });
});

///**
// * End2End test for admin survey module
// */
//describe('admin/survey/edit', function () {
//
//    beforeEach(function () {
//        //@todo
//        browser().navigateTo('/admin/survey/edit/1');
//        loginUser();
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
