$(document).ready(() => {
    window.getSanitizedValue = (val)=>{
        return val.replace(new RegExp(';', 'g'), '');
    };
    window.feedbackAlreadySent = false;
    window.feedbackData = {
        answers: {
            '1': { value: '' },
            '2': { value: '' },
            '3': { value: '' },
            '4': { value: '' },
            '5': { value: '' },
            '6': { value: '' },
            '7': { value: '' },
            '8': { value: '' },
            '9': { value: '' },
            '10': { value: '' },
            '11': { value: '' },
            '12': { value: '' },
            '13': { value: '' },
            '14': { value: '' },
            '15': { value: '' },
            '16': { value: '' },
            '17': { value: '' },
            '18': { value: '' },
            '19': { value: '' },
            '20': { value: '' },
            '21': { value: '' },
            '22': { value: '' },
            '23': { value: '' },
            '24': { value: '' },
            '25': { value: '' },
            '26': { value: '' },
            '27': { value: '' },
            '28': { value: '' },
            '29': { value: '' },
            '30': { value: '' },
        },
    };

    const $feedbackSubmitBtn = $('#feedback-submit-button');
    const $feedbackCancelBtn = $('#feedback-cancel-button');
    const $feedbackPrevBtn = $('#feedback-prev-button');
    const $feedbackNextBtn = $('#feedback-next-button');
    const $feedbackDoneBtn = $('#feedback-done-button');

    $('body').on('click', '.feedback-button', event => {
        if (window.feedbackAlreadySent) {
            window.location.reload();
        }
        $('#dropdownMenu-answer-26 ~ul li[value="de"]').trigger('click');
    });

    $('.nav-tabs > li a[title]').tooltip();

    $('a[data-toggle="tab"]').on('show.bs.tab', e => {
        const $target = $(e.target);

        if ($target.hasClass('disabled')) {
            return false;
        }
    });

    $('.next-step').on('click', e => {
        const $active = $('.wizard .nav-tabs .nav-item .active');
        const $activeli = $active.parent('li');

        $('.round-tab', $activeli).addClass('visited');

        if (
            $($activeli)
                .next()
                .find('.nav-link[data-toggle="tab"]')
                .hasClass('last-step')
        ) {
            $feedbackNextBtn.hide();
            $feedbackSubmitBtn.show();
        }

        $($activeli)
            .next()
            .find('.nav-link[data-toggle="tab"]')
            .removeClass('disabled');
        $($activeli)
            .next()
            .find('.nav-link[data-toggle="tab"]')
            .click();
    });

    $('.prev-step').on('click', e => {
        const $active = $('.wizard .nav-tabs .nav-item .active');
        const $activeli = $active.parent('li');

        $('.round-tab', $activeli).removeClass('visited');

        if (
            !$($activeli)
                .prev()
                .find('.nav-link[data-toggle="tab"]')
                .hasClass('last-step')
        ) {
            $feedbackSubmitBtn.hide();
            $feedbackNextBtn.show();
        }

        $($activeli)
            .prev()
            .find('.nav-link[data-toggle="tab"]')
            .removeClass('disabled');
        $($activeli)
            .prev()
            .find('.nav-link[data-toggle="tab"]')
            .click();
    });

    const translateStringToRateFeedback = val => {
        switch (val) {
            case '1':
                return 'totally disagree';
            case '2':
                return 'partially disagree';
            case '3':
                return 'neutral';
            case '4':
                return 'partially agree';
            case '5':
                return 'completely agree';
            default:
                return '';
        }
    };
    (selector => {
        const countryList = $(selector);
        const listitems = countryList.children('li').get();
        listitems.sort((a, b) => {
            return $(a)
                .text()
                .toUpperCase()
                .localeCompare(
                    $(b)
                        .text()
                        .toUpperCase(),
                );
        });
        $.each(listitems, (idx, itm) => {
            countryList.append(itm);
        });
    })('#dropdownMenu-answer-26 ~ ul');

    window.closeModal = () => {
        window.location.reload();
    };

    window.sendFeedback = () => {
        $('#feedback-modal button').attr('disabled', 'disabled');

        window.submitBtn = $('#feedback-submit-button');
        let dots = '.';

        const dotCounter = (dots => {
            return function () {
                dots = dots.length >= 3 ? '.' : dots + '.';
                window.submitBtn.text('sending' + dots);
            };
        })(dots);

        const interval = setInterval(dotCounter, 400);

        //1
        window.feedbackData.answers['1'].value = getSanitizedValue($('#dropdownMenu-answer-1')
            .parent()
            .attr('value'));

        //2
        let checkboxes = [];
        $('input[name="answer-2"]').each((idx, checkbox) => {
            if (checkbox.checked) {
                checkboxes.push(
                    $(checkbox)
                        .siblings('label')
                        .first()
                        .text()
                        .trim(),
                );
            } else {
                checkboxes.push('not checked');
            }
        });
        window.feedbackData.answers['2'].value =
            checkboxes.join(';') + ';' + $('#answer-free-text-2').val();

        //3-8
        for (let i = 3; i <= 8; i++) {
            window.feedbackData.answers[
                i
                ].value = getSanitizedValue(translateStringToRateFeedback(
                $(`input[name="answer-${i}"]:checked`).val()),
            );
        }

        //9
        window.feedbackData.answers['9'].value = $('#answer-9').val();

        //10-13
        for (let i = 10; i <= 13; i++) {
            window.feedbackData.answers[
                i
                ].value = getSanitizedValue(translateStringToRateFeedback(
                $(`input[name="answer-${i}"]:checked`).val()),
            );
        }

        //14-17
        for (let i = 14; i <= 17; i++) {
            window.feedbackData.answers[i].value = getSanitizedValue($(`#answer-${i}`).val());
        }

        //18-22
        for (let i = 18; i <= 22; i++) {
            window.feedbackData.answers[
                i
                ].value = getSanitizedValue(translateStringToRateFeedback(
                $(`input[name="answer-${i}"]:checked`).val()),
            );
        }

        //23
        window.feedbackData.answers['23'].value = $('#answer-23').val();

        //24-26
        for (let i = 24; i <= 26; i++) {
            window.feedbackData.answers[i].value = getSanitizedValue($(
                `#dropdownMenu-answer-${i} .dropdown-text-holder`,
            ).text());
        }

        //27-30
        for (let i = 27; i <= 30; i++) {
            window.feedbackData.answers[i].value = getSanitizedValue($(`#answer-${i}`).val());
        }

        let serializedFeedback = '';

        for (let i = 1; i <= 30; i++) {
            // strip ; for serialization, because its reserved in csv
            serializedFeedback += window.feedbackData.answers[i].value + ';';
        }

        $.ajax({
            url: '/api/betafeedback',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ feedback: serializedFeedback }),
        }).always(()=>{
            const successContent = $('.final-body').html();
            $('.modal-body').html(successContent);
            $('#feedback-modal button').removeAttr('disabled');
            $feedbackCancelBtn.hide(0);
            $feedbackPrevBtn.hide(0);
            $feedbackNextBtn.hide(0);
            $feedbackSubmitBtn.hide(0);
            $feedbackDoneBtn.show(0);
            clearInterval(interval);
            window.feedbackAlreadySent = true;
        });
    };
});
