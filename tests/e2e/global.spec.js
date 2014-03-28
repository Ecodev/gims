/* http://docs.angularjs.org/guide/dev_guide.e2e-testing */

var gimsUtility = require('./utility');

describe("Xdebug tests", function () {

    var pages = [
        '/home',
        '/about',
        '/browse',
        '/contribute',
        '/admin/question/new',
        '/admin/questionnaire/new',
        '/admin/survey',
        '/admin/survey/new',
        '/admin/filter-set',
        '/admin/filter-set/new',
        '/admin/filter',
        '/admin/filter/new'
    ];

    browser.get('/home');
    gimsUtility.login(undefined, undefined, browser);

    pages.forEach(function(page){
        it('should not have error on page '+page, function () {
            browser.get(page);
            expect(browser.getCurrentUrl()).toBe(browser.baseUrl+page);
            expect(element.all(by.css('.xdebug-error')).count()).toBe(0);
        });
    })
});

/**
 * End2End tests my app
 */
describe('my app', function () {
    'use strict';

    it('should automatically redirect to /home when location hash/fragment is empty', function () {
        browser.get('../../');
        expect(browser.getCurrentUrl()).toBe(browser.baseUrl+"/home");
    });
});
