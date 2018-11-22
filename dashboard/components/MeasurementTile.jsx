import React, { Fragment } from 'react';
import { ResultListPageCompact } from '../../result-list/components/ResultListPageCompact';

export const MeasurementTile = () => (
    <Fragment>
        <h4 className="green tile-heading">
            {window.translations.dashboard.yourMeasurements}
        </h4>

        <ResultListPageCompact />
    </Fragment>
);
