$(document).ready(function() {
    $('#modal-toggle-button').click(() => {
        $('#modal').modal('show');
    });

    window.renderCheckboxes = () => {
        $('input[type=button].checkbox')
            .off()
            .on('click', event => {
                const color = $(event.target).attr('data-color');
                const colorActive = 'on-' + color + '-active';
                const colorInactive = 'on-' + color + '-inactive';

                if ($(event.target).hasClass(colorActive)) {
                    $(event.target).removeClass(colorActive);
                    $(event.target).addClass(colorInactive);
                } else {
                    $(event.target).removeClass(colorInactive);
                    $(event.target).addClass(colorActive);
                }

                $(event.target)
                    .parent()
                    .find('input[type=checkbox]')
                    .click()
                    .change();
            });
    };
    renderCheckboxes();
});
