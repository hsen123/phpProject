require('../css/register.scss');
const H = require('./common/helper');

function RegisterViewModel() {
    this.email = ko.observable();
    this.password = ko.observable();
    this.passwordRepeat = ko.observable();
    this.disabled = ko.observable(true);
    this.tosChecked = ko.observable(false);
    this.secondStep = ko.observable(false);

    this.emailField = $('#userEmail')[0];
    this.passwordField = $('#passwordFirst')[0];
    this.passwordRepeatField = $('#passwordSecond')[0];
    this.registerForm = $('#register-form');

    this.email.subscribe(() => {
        if (typeof this.emailField.reportValidity === 'function') {
            this.emailField.reportValidity();
        }

        this.checkFormValidation();
    });

    this.password.subscribe(() => {
        if (typeof this.passwordField.reportValidity === 'function') {
            this.passwordField.reportValidity();
        }
        this.checkFormValidation();
    });

    this.passwordRepeat.subscribe(() => {
        if (typeof this.passwordRepeatField.reportValidity === 'function') {
            this.passwordRepeatField.reportValidity();
        }
        this.checkFormValidation();
    });

    this.continueButton = () => {
        if (this.disabled()) {
            return this.reportValidity();
        }
        ga('send', 'event', 'Register', 'step2');
        this.secondStep(true);
    };

    this.backButton = () => {
        this.secondStep(false);
    };

    this.createButton = () => {
        if (this.tosChecked()) {
            ga('send', 'event', 'Register', 'success');
            this.registerForm.submit();
        }
    };

    this.reportValidity = () => {
        if (typeof this.emailField.reportValidity !== 'function') {
            return;
        }

        this.emailField.reportValidity();
        this.passwordField.reportValidity();
        this.passwordRepeatField.reportValidity();
    };

    this.checkFormValidation = () => {
        this.disabled(
            !(
                this.emailField.checkValidity() &&
                this.passwordField.checkValidity() &&
                this.passwordRepeatField.checkValidity()
            ),
        );
    };
}

$(document).ready(() => {
	H.sortDropdown('#fos_user_registration_form_segment > ul');
	ko.applyBindings(new RegisterViewModel());
});
