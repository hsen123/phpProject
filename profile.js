require('../css/profile.scss');
const H = require('./common/helper');

$(document).ready(() => {
    animateProgressbar();
    opeModal('.edit-button', '#edit-user-data');
    opeModal('#password-button', '#edit-user-password');
    opeModal('.edit-company-button', '#edit-user-company');
    opeModal('.edit-segment-button', '#edit-user-segment');
    opeModal('#delete-user-button', '#delete-user');

    checkForDeleteTyped();
    H.sortDropdown('#user_company_companyCountry > ul');
    H.sortDropdown('#user_segment_segment > ul');
    H.sortDropdown('#user_segment_segmentPosition > ul');

    resetEditPasswordModalWhenHidden();

    $('#delete-button').click(() => {
        deleteUser();
    });

    $('#file-input').on('change', () => {
        const fileReader = new FileReader();
        fileReader.onload = function() {
            const data = fileReader.result; // data <-- in this var you have the file data in Base64 format
            const base64Contents = data.split(',')[1];

            const userId = $('#user-profile-image').data('userid');

            $.ajax({
                url: `/api/image/user/${userId}`,
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ profileImage: base64Contents }),
                success: () => {
                    window.location = '/profile';
                },
                error: (xhr, status, error) => {
                    if (xhr.status === 413) {
                        $('.image-too-large-error').css('display', 'block');
                    }
                },
            });
        };
        fileReader.readAsDataURL($('#file-input').prop('files')[0]);
    });
});

function resetEditPasswordModalWhenHidden() {
    $('#edit-user-password').on('hidden.bs.modal', function (event) {
        const modal = $(this);
        modal.find('.invalid-input').removeClass('invalid-input');
        modal.find('.alert').remove();
        modal.find('.form-control.input-white').val('');
    })
}

function animateProgressbar() {
    const progress = parseInt(
        $('.progressbar-wrapper').attr('data-profilPercent'),
    );
    const progressbar = $('.progressbar');
    let width = 1;

    let id = setInterval(() => {
        if (width >= progress) {
            clearInterval(id);
        } else {
            width = width + 1;
            const widthForCss = width + '%';
            progressbar.css('width', widthForCss);
        }
    }, 10);
}

function opeModal(openModalButtonClass, dataTarget) {
    $(openModalButtonClass)
        .first()
        .attr('data-toggle', 'modal');
    $(openModalButtonClass)
        .first()
        .attr('data-target', dataTarget);
}

function deleteUser() {
    const userId = $('#confirm-delete-input').data('userid');
    const loggedInUserId = $('#confirm-delete-input').data('loggedInUserId');

    $.ajax({
        url: `/api/users/` + userId,
        type: 'DELETE',
        contentType: 'application/json',
        dataType: 'json',
        success: () => {
            if (userId === loggedInUserId) {
                window.location = '/logout';
            } else {
							window.location = '/admin/dashboard';
            }
        },
    });
}

function checkForDeleteTyped() {
    const input = $('#confirm-delete-input');
    const button = $('#delete-button');

    input.keyup(() => {
        if (input.val() === input.attr('data-delete-text')) {
            button.prop('disabled', false);
        } else {
            button.prop('disabled', true);
        }
    });
}
