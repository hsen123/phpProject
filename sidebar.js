require('../css/sidebar.scss');

$(document).ready(function() {
    $('.icon-group').click(function(event) {
        $('.icon').removeClass('selected-icon');
        $(event.target.firstElementChild).addClass('selected');
    });
});
