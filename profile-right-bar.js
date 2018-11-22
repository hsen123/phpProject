require('../css/profile-right-bar.scss');

/**
 * Toggle checkbox to deselect the device and do not prevent default form submit
 */
function onLogoutDevice() {
    const targetCheckboxId = $(this).data('target-checkbox');
    const targetCheckbox = $(`#${targetCheckboxId}`);
    targetCheckbox.prop('checked', !targetCheckbox.prop('checked'));
}

$(() => {
    const $deviceLogoutButtons = $('.logout-device-button');
    $deviceLogoutButtons.on('click', onLogoutDevice);
});
