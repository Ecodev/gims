/* http://docs.angularjs.org/guide/dev_guide.e2e-testing */

/**
 * Common function to assert no xdebug error. Should be used at least after each browser().navigateTo()
 */
function noXdebugError() {
    'use strict';
    describe("any page", function () {
        it('should not have any xdebug error at all', function () {
            expect(element('.xdebug-error').count()).toBe(0);
        });
    });
}

/**
 * End2End tests my app
 */
describe('my app', function () {
    'use strict';

    beforeEach(function () {
        browser().navigateTo('../../');

    });
    noXdebugError();

    it('should automatically redirect to /home when location hash/fragment is empty', function () {
        expect(browser().location().url()).toBe("/home");
    });

    /**
     * End2End tests for home module
     */
    describe('home', function () {

        beforeEach(function () {
            browser().navigateTo('/home');
        });
        noXdebugError();

        it('should render home when user navigates to /home', function () {
            expect(element('[ng-view] p:first').text()).
                toMatch(/Global Information Management System/);
        });
    });

    /**
     * End2End tests for about module
     */
    describe('about', function () {

        beforeEach(function () {
            browser().navigateTo('/about');
        });
        noXdebugError();


        it('should render about when user navigates to /about', function () {
            expect(element('[ng-view] p:first').text()).
                toMatch(/Learn where the project stems from and what are its goals/);
        });

    });

    /**
     * End2End tests for browse module
     */
    describe('browse', function () {

        beforeEach(function () {
            browser().navigateTo('/browse');
        });
        noXdebugError();


        it('should render about when user navigates to /browse', function () {
            expect(element('[ng-view] .browse .span4:nth-child(1) p').text()).
                toMatch(/Maps/);

            expect(element('[ng-view] .browse .span4:nth-child(2) p').text()).
                toMatch(/Charts/);

            expect(element('[ng-view] .browse .span4:nth-child(3) p').text()).
                toMatch(/Tables/);
        });

    });

    /**
     * End2End tests for contribute module
     */
    describe('contribute', function () {

        beforeEach(function () {
            browser().navigateTo('/contribute');
        });

        noXdebugError();


        it('should render contribute when user navigates to /contribute', function () {
            expect(element('[ng-view] p:first').text()).
                toMatch(/Small streams make large rivers/);
        });

        it('should render admin buttons', function () {

            expect(element('[ng-view] .span4:nth-child(1) li:nth-child(1) p').text()).
                toMatch(/Surveys/);

            expect(element('[ng-view] .span4:nth-child(1) li:nth-child(2) p').text()).
                toMatch(/Users/);
        });

        it('should render other buttons', function () {
            expect(element('[ng-view] .span4:nth-child(2) li:nth-child(1) p').text()).
                toMatch(/Questionnaires/);
        });
    });

    /**
     * End2End tests for admin module
     */
    describe('admin', function () {

        beforeEach(function () {
            browser().navigateTo('/admin');
        });

        noXdebugError();

        it('should redirect to /contribute when user navigates to /admin', function () {
            expect(browser().location().path()).toMatch('/contribute');
        });

        /**
         * End2End test for admin survey module
         */
        describe('admin/survey', function () {

            beforeEach(function () {
                browser().navigateTo('/admin/survey');
            });

            noXdebugError();

            it('should display a grid containing surveys', function () {
                expect(element('[ng-view] [ng-grid]').count()).toBe(1);
            });

            it('should contains a grid with more than 0 element', function () {
                expect(element('[ng-view] [ng-grid] .ngCanvas').count()).toBeGreaterThan(0);
            });

            it('should contains a link for creating new survey', function () {
                expect(element('[ng-view] .link-new').count()).toBe(1);
            });

            it('should lead to /admin/survey/new when following link new survey', function () {
                element('[ng-view] .link-new').click();
                expect(browser().location().path()).toMatch('/admin/survey/new');
            });
        });

        /**
         * End2End test for admin survey module
         */
        describe('admin/survey/new', function () {

            beforeEach(function () {
                browser().navigateTo('/admin/survey/new');
            });

            // Computes random code
            var randomCode;
            randomCode = Math.random().toString(36).substr(2, 4);

            function fillSurveyForm() {
                input('survey.code').enter(randomCode);
                input('survey.name').enter('foo');
                select('survey.active').option(0);
                input('survey.year').enter('2013');
                input('survey.comments').enter('foo bar');
                input('survey.dateStart').enter('08/05/2013');
                input('survey.dateEnd').enter('08/05/2014');
            }

            noXdebugError();

            it('should be able to fill-in required fields', function () {
                expect(element('[ng-view] .btn-save[disabled]').count())
                    .toBe(1);

                expect(element('[ng-view] .btn-saving').count())
                    .toBe(0);

                fillSurveyForm();

                expect(element('[ng-view] .btn-save[disabled]').count())
                    .toBe(0);
            });

            it('should be displayed tabs', function () {
                var panes = new Array('General info', 'Question', 'Questionnaires', 'Users');
                for (var index = 0; index < panes.length; index++) {
                    var paneText = panes[index];
                    expect(element('[ng-view] .nav-tabs li:eq(' + index + ')').text())
                        .toMatch(paneText);
                }
            });


            it('should be able to save a new survey and delete it', function () {

                fillSurveyForm();

                // Click save button
                element('[ng-view] .btn-save').click();

                // Check if the element was found in survey list
                browser().navigateTo('/admin/survey');
                expect(element('[ng-view] [ng-grid] span:contains("' + randomCode + '")').count())
                    .toBe(1);
                // @todo delete is currently not implemented
            });
        });
    });

});
