import React from 'react';
import PropTypes from 'prop-types';

export const CITATION = [
    <span>
        NO<sub>3</sub>
        <sup>-</sup>
    </span>,
    <span>pH</span>,
    <span>
        NO<sub>3</sub>
        <sup>-</sup> / pH
    </span>,
];

export const CITATION_NO3 = 0;
export const CITATION_PH = 1;
export const CITATION_ALL = 2;

export function Citation({ constant }) {
    return CITATION[constant] || <span>{constant}</span>;
}

Citation.propTypes = {
    constant: PropTypes.number,
};
