import React from 'react';
import { render } from 'react-dom';
import { ActivityTile } from './components/ActivityTile';
import { AnalysisListTile } from './components/AnalysisListTile';
import { MeasurementTile } from './components/MeasurementTile';
import { PackageCounterTile } from './components/PackageCounterTile';

import './dashboard.less';

render(
    <ActivityTile />,
    document.getElementById('react-dashboard-activity-tile'),
);
render(
    <AnalysisListTile />,
    document.getElementById('react-dashboard-analysis-list-tile'),
);
render(
    <MeasurementTile />,
    document.getElementById('react-dashboard-measurement-tile'),
);

const packageCounterRoot = document.getElementById(
    'react-dashboard-package-counter-tile',
);

render(
    <PackageCounterTile
        no3={JSON.parse(packageCounterRoot.dataset.no3)}
        ph={JSON.parse(packageCounterRoot.dataset.ph)}
    />,
    packageCounterRoot,
);
