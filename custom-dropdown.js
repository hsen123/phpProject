require('../css/custom-dropdown.scss');

$(document).ready(function() {
    $('.dropdown-element').click(event => {
        const newValue = $(event.target).data('value');
        const text = $(event.target).text();
        const parent = $(event.target)
            .parent()
            .parent();

        parent.find('.dropdown-text-holder').text(text);
        parent.closest('.dropdown').attr('value', newValue);
        parent
            .parent()
            .find('input[type=hidden]')
            .attr('value', newValue);
    });

    $('.special-dropdown ~ul .dropdown-element').click(event => {
        const newValue = $(event.target).attr('value');
        const text = $(event.target).text();
        const parent = $(event.target)
            .parent()
            .parent();

        parent.find('.dropdown-text-holder').text(text);
        parent.closest('.dropdown').attr('value', newValue);
        parent.attr('value', newValue);
    });
    $('.special-dropdown ~ul .dropdown-element:first-of-type').trigger('click');
});
