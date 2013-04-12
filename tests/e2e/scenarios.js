'use strict';

/* http://docs.angularjs.org/guide/dev_guide.e2e-testing */


/**
 * Common function to assert no xdebug error. Should be used at least after each browser().navigateTo()
 */
function noXdebugError() {
    describe("any page", function() {
        it('should not have any xdebug error at all', function() {
            expect(element('.xdebug-error').count()).toBe(0);
        });
    });
}


describe('my app', function() {

    beforeEach(function() {
        browser().navigateTo('../../');

    });
    noXdebugError();


    it('should automatically redirect to /home when location hash/fragment is empty', function() {

        expect(browser().location().url()).toBe("/home");
    });


    describe('home', function() {

        beforeEach(function() {
            browser().navigateTo('/home');
        });
        noXdebugError();


        it('should render home when user navigates to /home', function() {
            expect(element('[ng-view] p:first').text()).
                    toMatch(/Global Information Management System/);
        });

    });


    describe('about', function() {

        beforeEach(function() {
            browser().navigateTo('/about');
        });
        noXdebugError();


        it('should render about when user navigates to /about', function() {
            expect(element('[ng-view] p:first').text()).
                    toMatch(/Learn where the project stems from and what are its goals/);
        });

    });


    describe('browse', function() {

        beforeEach(function() {
            browser().navigateTo('/browse');
        });
        noXdebugError();


        it('should render about when user navigates to /browse', function() {
            expect(element('[ng-view] .browse .span4:nth-child(1) h2').text()).
                    toMatch(/Maps/);

            expect(element('[ng-view] .browse .span4:nth-child(2) h2').text()).
                    toMatch(/Charts/);

            expect(element('[ng-view] .browse .span4:nth-child(3) h2').text()).
                    toMatch(/Tables/);
        });

    });

    describe('contribute', function() {

        beforeEach(function() {
            browser().navigateTo('/contribute');
        });
        noXdebugError();


        it('should render contribute when user navigates to /contribute', function() {
            expect(element('[ng-view] p:first').text()).
                    toMatch(/Small streams make large rivers/);
        });

        it('should render sign in form', function() {
            expect(element('body > .container form[action="/user/login"] button').text()).
                    toMatch(/Sign In/);
        });

        it('should render account creation form', function() {
            expect(element('body > .container form[action="/user/register"] button').text()).
                    toMatch(/Register/);
        });
    });

    describe('admin', function () {

        beforeEach(function () {
            browser().navigateTo('/admin');
        });

        noXdebugError();

        it('should render admin when user navigates to /admin', function () {
            expect(element('[ng-view] p:first').text()).
                toMatch(/Small streams make large rivers/);
        });

        it('should render module Survey', function () {
            expect(element('body > .container .container-survey h2').text()).
                toMatch(/Survey/);
        });

        it('should render module Questionnaire', function () {
            expect(element('body > .container .container-questionnaire h2:nth-child(1)').text()).
                toMatch(/Questionnaire/);
        });
    });
});
