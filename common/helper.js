import React, { Fragment } from 'react';

// https://stackoverflow.com/questions/5999118/how-can-i-add-or-update-a-query-string-parameter#answer-6021027
function updateQueryStringParameter(uri, key, value) {
    const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    const separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    }
    else {
        return uri + separator + key + "=" + value;
    }
}

module.exports = {
    getParamByName: (name, url) => {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    },
    getRGBFromHSLLightnessValue: (value, maxvalue) => {
        let lightness = 0;
        // von 10 bis 90
        if (value > maxvalue) {
            lightness = 90;
        } else {
            lightness = parseInt(90 / maxvalue * value);
        }
        return `hsl(277,100%,${lightness}%)`;
    },

    /**
     * Simple object check.
     * @param item
     * @returns {boolean}
     */
    isObject: item => {
        return item && typeof item === 'object' && !Array.isArray(item);
    },

    /**
     * Deep merge two objects.
     * @param target
     * @param ...sources
     */
    mergeDeep: (target, ...sources) => {
        const isObject = item => {
            return item && typeof item === 'object' && !Array.isArray(item);
        };

        if (!sources.length) return target;
        const source = sources.shift();

        if (isObject(target) && isObject(source)) {
            for (const key in source) {
                if (isObject(source[key])) {
                    if (!target[key]) Object.assign(target, { [key]: {} });
                    this.mergeDeep(target[key], source[key]);
                } else {
                    Object.assign(target, { [key]: source[key] });
                }
            }
        }

        return this.mergeDeep(target, ...sources);
    },

    citationToName: citationEnum => {
        return $(`#trans-citation-${citationEnum}`).text() || '';
    },
    segmentEnumToName: segmentEnum => {
        return $(`#trans-segment-${segmentEnum}`).text() || '';
    },

    setCaretAtEnd: elem => {
        const elemLen = elem.value.length;
        // For IE Only
        if (document.selection) {
            // Set focus
            elem.focus();
            // Use IE Ranges
            const oSel = document.selection.createRange();
            // Reset position to 0 & then set at end
            oSel.moveStart('character', -elemLen);
            oSel.moveStart('character', elemLen);
            oSel.moveEnd('character', 0);
            oSel.select();
        } else if (elem.selectionStart || elem.selectionStart == '0') {
            // Firefox/Chrome
            elem.selectionStart = elemLen;
            elem.selectionEnd = elemLen;
            elem.focus();
        } // if
    },
    togglePlotMap: () => {
        const plotButton = $('#plot-button');
        const mapButton = $('#map-button');
        const plotFilter = $('.result-plot');
        const mapFilter = $('.result-map');

        plotButton.click(() => {
            plotButton.removeClass('primary');
            plotButton.addClass('chips');

            mapButton.removeClass('chips');
            mapButton.addClass('primary ');

            plotFilter.css('display', 'block');
            mapFilter.css('display', 'none');
        });

        mapButton.click(() => {
            mapButton.removeClass('primary');
            mapButton.addClass('chips');

            plotButton.removeClass('chips');
            plotButton.addClass('primary ');

            plotFilter.css('display', 'none');
            mapFilter.css('display', 'block');
        });
    },
    getImageColor: (unit, value) => {
        value = value > 500 ? 501 : value;

        if (unit === 'NO3' || unit === 0) {
            switch (true) {
                case value >= 0 && value < 5:
                    return 'nitrate-0';
                case value >= 5 && value < 10:
                    return 'nitrate-5';
                case value >= 10 && value < 15:
                    return 'nitrate-10';
                case value >= 15 && value < 20:
                    return 'nitrate-15';
                case value >= 20 && value < 25:
                    return 'nitrate-20';
                case value >= 25 && value < 35:
                    return 'nitrate-25';
                case value >= 35 && value < 50:
                    return 'nitrate-35';
                case value >= 50 && value < 75:
                    return 'nitrate-50';
                case value >= 75 && value < 100:
                    return 'nitrate-75';
                case value >= 100 && value < 250:
                    return 'nitrate-100';
                case value >= 250 && value < 500:
                    return 'nitrate-250';
                case value === 500:
                    return 'nitrate-500';
                case value >= 501:
                    return 'nitrate-501';
            }
        } else if (unit === 'pH' || unit === 1) {
            switch (true) {
                case value === 0 && value < 0.5:
                    return 'ph-0';
                case value >= 0.5 && value < 1:
                    return 'ph-0_5';
                case value >= 1 && value < 1.5:
                    return 'ph-1';
                case value >= 1.5 && value < 2:
                    return 'ph-1_5';
                case value >= 2 && value < 2.5:
                    return 'ph-2';
                case value >= 2.5 && value < 3:
                    return 'ph-2_5';
                case value >= 3 && value < 3.5:
                    return 'ph-3';
                case value >= 3.5 && value < 4:
                    return 'ph-3_5';
                case value >= 4 && value < 4.5:
                    return 'ph-4';
                case value >= 4.5 && value < 5:
                    return 'ph-4_5';
                case value >= 5 && value < 5.5:
                    return 'ph-5';
                case value >= 5.5 && value < 6:
                    return 'ph-5_5';
                case value >= 6 && value < 6.5:
                    return 'ph-6';
                case value >= 6.5 && value < 7:
                    return 'ph-6_5';
                case value >= 7 && value < 7.5:
                    return 'ph-7';
                case value >= 7.5 && value < 8:
                    return 'ph-7_5';
                case value >= 8 && value < 8.5:
                    return 'ph-8';
                case value >= 8.5 && value < 9:
                    return 'ph-8_5';
                case value >= 9 && value < 9.5:
                    return 'ph-9';
                case value >= 9.5 && value < 10:
                    return 'ph-9_5';
                case value >= 10 && value < 10.5:
                    return 'ph-10';
                case value >= 10.5 && value < 11:
                    return 'ph-10_5';
                case value >= 11 && value < 11.5:
                    return 'ph-11';
                case value >= 11.5 && value < 12:
                    return 'ph-11_5';
                case value >= 12 && value < 12.5:
                    return 'ph-12';
                case value >= 12.5 && value < 13:
                    return 'ph-12_5';
                case value >= 13 && value < 13.5:
                    return 'ph-13';
                case value >= 13.5 && value < 14:
                    return 'ph-13_5';
                case value >= 14:
                    return 'ph-14';
            }
        }

        return 'ph-0';
    },
    sortDropdown: selector => {
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
    },
    prepareButton: (button, targetModal) => {
        button.first().attr('data-toggle', 'modal');
        button.first().attr('data-target', targetModal);
    },
    prepareExportLink: (button) => {
        if (!button) {
            return;
        }
        const target = button.first();
        if (!target) {
            return;
        }
        const timezoneOffset = -new Date().getTimezoneOffset();
        const link = target.attr('href');
        const updatedLink = updateQueryStringParameter(link, "tzo",timezoneOffset);
        target.attr('href', updatedLink);
    },
    debounce: (delay, fn) => {
        let timerId;
        return function(...args) {
            if (timerId) {
                clearTimeout(timerId);
            }
            timerId = setTimeout(() => {
                fn(...args);
                timerId = null;
            }, delay);
        };
    },
    resolveLatestResponse: asyncFn => {
        let sequence = 0;

        return (...args) =>
            new Promise((resolve, reject) => {
                // memorize the incremented sequence number
                sequence += 1;
                const localSequence = sequence;
                asyncFn(...args)
                    .then((...resArgs) => {
                        // just resolve the promise with the latest sequence number
                        // non resolved promises will be garbage collected
                        if (localSequence === sequence) {
                            resolve(...resArgs);
                        }
                    })
                    .catch(reject);
            });
    },
    /**
     * Replaces a substring with an element
     * e.g.
     * replaceWithElement"Hello %name%, nice to meet you.", "%name%", <span>{name}</span>);
     * // -> ['Hello ', <span>{name}</span>, ', nice to meet you.']
     *
     * @param {string} target
     * @param {string} search
     * @param {any} element
     * @returns {*}
     */
    replaceWithElement(target, search, element) {
        const index = target.indexOf(search);
        if (index === -1) {
            return target;
        }
        const { length } = search;
        const first = target.slice(0, index);
        const second = target.slice(index + length, target.length);

        return (
            <Fragment>
                {first}
                {element}
                {second}
            </Fragment>
        );
    }
};
