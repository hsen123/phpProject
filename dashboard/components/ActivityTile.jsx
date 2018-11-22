import React, { Component, Fragment } from 'react';
import Button from 'antd/lib/button';
import { Legend } from 'recharts';
import moment from 'moment';
import { ActivityQuery } from '../container/ActivityQuery';
import { ActivityPieChart } from './ActivityPieChart';
import { Loading } from '../../common/components/Loading';
import { ActivityWeekChart } from './ActivityWeekChart';
import { NITRATE_COLOR, PH_COLOR } from '../../result-list/plot/ResultPlot';
import {
    Citation,
    CITATION_NO3,
    CITATION_PH,
} from '../../common/components/Citation';
import leftIcon from '../../../images/icons/material-design/gray/left.svg';
import rightIcon from '../../../images/icons/material-design/gray/right.svg';

export class ActivityTile extends Component {
    renderNoData = () => (
        <div className="no-content">
            <span>{window.translations.general.noDataLong}</span>
        </div>
    );

    renderCharts = data => (
        <Fragment>
            <div className="activity-charts">
                <div>
                    <ActivityPieChart data={data ? data.pie : null} />
                </div>
                <div>
                    <ActivityWeekChart data={data ? data.chart : null} />
                </div>
            </div>
            <div className="activity-legend">
                <Legend
                    style={{
                        position: 'relative',
                    }}
                    payload={[
                        {
                            inactive: false,
                            dataKey: 1,
                            type: 'rect',
                            color: PH_COLOR,
                            value: <Citation constant={CITATION_PH} />,
                        },
                        {
                            inactive: false,
                            dataKey: 0,
                            type: 'rect',
                            color: NITRATE_COLOR,
                            value: <Citation constant={CITATION_NO3} />,
                        },
                    ]}
                />
            </div>
        </Fragment>
    );

    render() {
        return (
            <Fragment>
                <h4 className="green tile-heading">
                    {window.translations.dashboard.yourActivity}
                </h4>
                <ActivityQuery>
                    {(
                        { data, error, loading, timestamp },
                        { fetchPrevActivity, fetchNextActivity },
                    ) => {
                        const noData =
                            !loading &&
                            (!data ||
                                !Array.isArray(data.pie) ||
                                !data.pie.length ||
                                !Array.isArray(data.chart) ||
                                !data.chart.length);

                        return (
                            <Fragment>
                                <div className="inline-container">
                                    <span className="heading">
                                        {
                                            window.translations.resultList
                                                .calendarWeekHeading
                                        }{' '}
                                        {moment(timestamp).format('w')}
                                    </span>
                                </div>
                                <Button
                                    disabled={loading}
                                    onClick={fetchPrevActivity}
                                    className="button-reset week-switch switch-left"
                                    style={{
                                        backgroundImage: `url(${leftIcon})`,
                                    }}
                                />
                                <Button
                                    disabled={loading}
                                    onClick={fetchNextActivity}
                                    className="button-reset week-switch switch-right"
                                    style={{
                                        backgroundImage: `url(${rightIcon})`,
                                    }}
                                />
                                <div className="activity-content">
                                    <Loading loading={loading}>
                                        {noData
                                            ? this.renderNoData()
                                            : this.renderCharts(data)}
                                    </Loading>
                                </div>
                            </Fragment>
                        );
                    }}
                </ActivityQuery>
            </Fragment>
        );
    }
}
