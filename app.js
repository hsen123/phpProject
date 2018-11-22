// assets/js/app.js
require('../css/app.scss');
require('./form-elements');
require('./custom-dropdown');
require('./register');

// require jQuery normally
const $ = require('jquery');
require('./common/feedback-modal');
const ko = require('../../node_modules/knockout/build/output/knockout-latest');
// create global $ and jQuery variables
global.$ = global.jQuery = $;
global.ko = ko;
$.ajaxSetup({ cache: false });

const z = ko.bindingHandlers.textInput.init;
ko.bindingHandlers.textInput.init = function(
    element,
    valueAccessor,
    allBindings,
) {
    if (allBindings.has('initWithElementValue')) {
        valueAccessor()(element.value);
    }
    z.apply(this, arguments);
};

const y = ko.bindingHandlers.value.init;
ko.bindingHandlers.value.init = function(element, valueAccessor, allBindings) {
    if (allBindings.has('initWithElementValue')) {
        valueAccessor()(element.value);
    }
    y.apply(this, arguments);
};

const origOpen = XMLHttpRequest.prototype.open;
XMLHttpRequest.prototype.open = function() {
    this.addEventListener('readystatechange', function() {
        if (this.readyState !== 4) {
            return;
        }

        if (this.status === 401) {
            window.location = '/logout';
        }
    });
    origOpen.apply(this, arguments);
};

$(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
