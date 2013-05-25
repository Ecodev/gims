/* http://docs.angularjs.org/guide/dev_guide.e2e-testing */

describe("any page", function () {
    it('should not have any xdebug error at all', function () {
        var pages;
        pages = new Array(
            '/home',
            '/about',
            '/browser',
            '/contribute',
            '/admin',
            '/admin/question',
            '/admin/question/new',
            '/admin/questionnaire',
            '/admin/questionnaire/new',
            '/admin/survey',
            '/admin/survey/new'
        );
        for (var index = 0; index < pages.length; index++) {
            browser().navigateTo(pages[index]);
            loginUser();
            expect(element('.xdebug-error').count()).toBe(0);
        }
    });
});

/**
 * End2End tests my app
 */
describe('my app', function () {
    'use strict';

    beforeEach(function () {
        browser().navigateTo('../../');

    });

    it('should automatically redirect to /home when location hash/fragment is empty', function () {
        expect(browser().location().url()).toBe("/home");
    });
});
