import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import Button from 'antd/lib/button';
import { BurndownChart } from '../container/BurndownChart';
import { PackageCounterInfo } from './PackageCounterInfo';
import { StartPackageCounterModal } from '../container/StartPackageCounterModal';
import { PackageCounterQuery } from '../container/PackageCounterQuery';
import {
    Citation,
    CITATION_NO3,
    CITATION_PH,
} from '../../common/components/Citation';
import { replaceWithElement } from '../../common/helper';
import { NITRATE_COLOR, PH_COLOR } from '../../result-list/plot/ResultPlot';

export const DAY_IN_SECONDS = 86400;

const fillNo3 = '#CFE1EF';
const strokeNo3 = NITRATE_COLOR;
const strokePh = PH_COLOR;
const fillPh = '#E7F6F5';

const no3Title = replaceWithElement(
    window.translations.packageCounter.startNew.title,
    '%citation%',
    <Citation constant={CITATION_NO3} />,
);

const phTitle = replaceWithElement(
    window.translations.packageCounter.startNew.title,
    '%citation%',
    <Citation constant={CITATION_PH} />,
);

export class PackageCounterTile extends Component {
    updateCountFor = citation => delta => {
        this.setState(({ [citation]: c }) => ({
            [citation]: {
                ...c,
                startAmount: c.startAmount + delta,
                amountOfTestStripsLeft: c.amountOfTestStripsLeft + delta,
            },
          loading: false,
        }));
    };

    constructor(props, ...args) {
        super(props, ...args);
        const { ph, no3 } = this.props;
        this.state = { ph, no3 };
    }

    render() {
        const { no3, ph } = this.state;

        return (
            <PackageCounterQuery
                onUpdateNO3Count={this.updateCountFor("no3")}
                onUpdatePHCount={this.updateCountFor("ph")}
            >
                {(_, { createPackageCounter, adjustPackageCounter }) => (
                    <Fragment>
                        <h4 className="green tile-heading">
                            {window.translations.dashboard.packageCounter}
                        </h4>
                        <BurndownChart
                            data={no3}
                            fill={fillNo3}
                            stroke={strokeNo3}
                        />
                        <PackageCounterInfo
                            type={CITATION_NO3}
                            adjustPackageCounter={adjustPackageCounter}
                            packageCounter={no3}
                            strokeColor={strokeNo3}
                        />
                        <StartPackageCounterModal
                            packageCounter={no3}
                            title={no3Title}
                            citationForm={CITATION_NO3}
                            onSubmit={createPackageCounter}
                        />
                        <BurndownChart
                            data={ph}
                            fill={fillPh}
                            stroke={strokePh}
                        />
                        <PackageCounterInfo
                            type={CITATION_PH}
                            adjustPackageCounter={adjustPackageCounter}
                            packageCounter={ph}
                            strokeColor={strokePh}
                        />
                        <StartPackageCounterModal
                            citationForm={CITATION_PH}
                            title={phTitle}
                            packageCounter={ph}
                            onSubmit={createPackageCounter}
                        />
                        <Button
                            className="button-reset btn button-large"
                            target="_blank"
                            rel="noopener noreferrer"
                            type="secondary"
                            href="http://www.merckmillipore.com/DE/de"
                        >
                            {window.translations.packageCounter.buyNew}
                        </Button>
                    </Fragment>
                )}
            </PackageCounterQuery>
        );
    }
}

PackageCounterTile.propTypes = {
    ph: PropTypes.object.isRequired,
    no3: PropTypes.object.isRequired,
};
