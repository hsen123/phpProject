require('../css/right-bar.scss');
const H = require('./common/helper');

$(document).ready(() => {
    H.prepareButton($('.edit-button'), '#edit-result');
    H.prepareButton($('#share-button'), '#share-result');
    H.prepareButton($('#snapshot-button'), '#export-result');

    H.prepareExportLink($('#csv-button'));
    H.prepareExportLink($('#excel-button'));
    H.prepareExportLink($('#zip-button'));

    /**
     * Eventlistener
     */
    window.deleteResult = () => {
        const idOfResultToBeDeleted = $('#label-row-id').data('id');
        $.ajax({
            url: `/api/results/${idOfResultToBeDeleted}`,
            type: 'DELETE',
            contentType: 'application/json',
            dataType: 'json',
            success: () => {
                window.location = window.location.origin;
            },
        });
    };
});
