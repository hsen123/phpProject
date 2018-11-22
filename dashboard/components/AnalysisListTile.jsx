import React, { Fragment } from 'react';
import { AnalysisPageCompact } from '../../analysis/components/AnalysisPageCompact';

export const AnalysisListTile = () => (
    <Fragment>
        <h4 className="green tile-heading">
            {window.translations.dashboard.yourAnalyses}
        </h4>

        <div className="row analyses-filtering-row no-gutters">
            <div className="col-sm-12">
                <AnalysisPageCompact />
            </div>
        </div>
    </Fragment>
);
