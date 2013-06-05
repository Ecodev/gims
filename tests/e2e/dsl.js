/**
 * Log in a newly registered user, if not yet logged in
 */
angular.scenario.dsl('loginUser', function() {
    return function() {

        return this.addFutureAction('try login', function(appWindow, $document, done) {

            var registerButton = $document.find('form[name="registerForm"] button');
            if (registerButton.length) {

                var supportInputEvent = 'oninput' in document.createElement('div');

                // Computes unique email
                var uniqueEmail = new Date().getTime() + '@example.com';

                // Set same value for all our inputs (not really normal use-case, but convenient)
                var inputs = $document.find('form[name="registerForm"] input');
                inputs.val(uniqueEmail);

                // Propagate changes, so our Angular controller can get the form values back later
                inputs.trigger((supportInputEvent ? 'input' : 'change'));

                // Click on the button (and thus call our Angular controller action to post data to backend)
                registerButton.trigger('click');
            }

            done();
        });
    };
});

