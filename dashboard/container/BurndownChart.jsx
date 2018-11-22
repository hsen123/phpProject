import React, { Component } from 'react';
import PropTypes from 'prop-types';
import {
    Line,
    XAxis,
    YAxis,
    ComposedChart,
    Area,
    CartesianGrid,
    ResponsiveContainer,
} from 'recharts';
import regression from 'regression';
import moment from 'moment';
import { DAY_IN_SECONDS } from '../components/PackageCounterTile';

function mapChartData(packageCounter, startValue = 100) {
    if (
        !packageCounter ||
        !Array.isArray(packageCounter.results) ||
        !packageCounter.results.length
    ) {
        return [];
    }

    const results = [...packageCounter.results].sort(
        ({ sampleCreationDate: a }, { sampleCreationDate: b }) => {
            if (a < b) {
                return -1;
            }

            if (a > b) {
                return 1;
            }

            return 0;
        },
    );

    const mappedResults = results.map((result, index) => ({
        x: result.sampleCreationDate / DAY_IN_SECONDS,
        y: startValue - index - 1,
    }));

    const todaysValue = {
        x: Date.now() / 1000 / DAY_IN_SECONDS,
        y: mappedResults[mappedResults.length - 1].y,
    };

    return [...mappedResults, todaysValue];
}

function findLastDate(data) {
    return data.length > 0 ? data[data.length - 1].x : 0;
}

function predictX(data) {
    const { equation: [m, c] } = regression.linear(
        data.map(item => [item.x, item.y]),
    );
    return isFinite(m) ? -c / m : findLastDate(data);
}

export class BurndownChart extends Component {
    static propTypes = {
        fill: PropTypes.string.isRequired,
        stroke: PropTypes.string.isRequired,
        data: PropTypes.shape({
            results: PropTypes.array,
            startAmount: PropTypes.number,
        }),
    };

    state = {
        regressionX: 0,
        data: [],
        origData: null,
    };

    static getDerivedStateFromProps(nextProps, prevState) {
        if (
            nextProps.data &&
            Array.isArray(nextProps.data.results) &&
            prevState.origData !== nextProps.data
        ) {
            if (nextProps.data.results.length < 2) {
                return { regressionX: 0, origData: nextProps.data, data: [] };
            }
            const data = mapChartData(
                nextProps.data,
                nextProps.data.startAmount,
            );
            const lastItem = data[data.length - 1];
            const x = Math.abs(predictX(data));
            data.push(
                {
                    ...lastItem,
                    regression: lastItem.y,
                },
                {
                    ...lastItem,
                    x,
                    y: undefined,
                    regression: 0,
                },
            );

            return {
                regressionX: x,
                data,
                origData: nextProps.data,
                yMax: nextProps.data.startAmount,
            };
        }
        return null;
    }

    render() {
        const { fill, stroke } = this.props;
        const { data, yMax } = this.state;
        if (!Array.isArray(data)) {
            return null;
        }

        return (
            <ResponsiveContainer width="95%" height={150}>
                <ComposedChart
                    data={data}
                    margin={{ top: 5, right: 5, left: -25, bottom: 0 }}
                >
                    <CartesianGrid vertical={false} />
                    <XAxis
                        dataKey="x"
                        type="number"
                        tickLine={false}
                        axisLine={false}
                        domain={['dataMin', 'dataMax']}
                        tickFormatter={v =>
                            moment.unix(v * DAY_IN_SECONDS).format('MMM YYYY')
                        }
                        tick={{ fontSize: '0.5em' }}
                        ticks={
                            data.length
                                ? [data[0].x, data[data.length - 1].x]
                                : []
                        }
                    />
                    <YAxis
                        domain={[0, 100]}
                        tickLine={false}
                        axisLine={false}
                        tick={{ fontSize: '0.5em' }}
                        tickFormatter={value =>
                            value === 100 || value === 0 ? `${value}` : ''
                        }
                    />
                    <Area
                        type="monotone"
                        dataKey="y"
                        strokeWidth="2"
                        fill={fill}
                        stroke={stroke}
                    />
                    <Line
                        type="monotone"
                        dataKey="regression"
                        dot={false}
                        stroke={stroke}
                        strokeWidth="2"
                        strokeDasharray="8 8"
                    />
                </ComposedChart>
            </ResponsiveContainer>
        );
    }
}
