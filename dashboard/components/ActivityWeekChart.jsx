import React, { Component } from 'react';
import {
    BarChart,
    Bar,
    XAxis,
    CartesianGrid,
    Legend,
    ResponsiveContainer,
    YAxis,
} from 'recharts';
import { CITATION_NO3, CITATION_PH } from '../../common/components/Citation';
import { NITRATE_COLOR, PH_COLOR } from '../../result-list/plot/ResultPlot';

function transformData(data) {
    return data.reduce(
        (acc, item) => {
            const date = new Date(item.sampleCreationDate * 1000);
            const day = date.getDay();
            // sunday is at the last index
            const index = day === 0 ? 6 : day === 6 ? 0 : day - 1;
            acc[index][item.citationForm]++;
            return acc;
        },
        (() => {
            const week = [];
            for (let i = 1; i < 7; i++) {
                week.push({
                    [CITATION_PH]: 0,
                    [CITATION_NO3]: 0,
                    day: window.translations.general.weekday.short[i],
                });
            }
            // add Sunday at the last index
            week.push({
                [CITATION_PH]: 0,
                [CITATION_NO3]: 0,
                day: window.translations.general.weekday.short[0],
            });
            return week;
        })(),
    );
}

export class ActivityWeekChart extends Component {
    static defaultProps = {
        colors: { [CITATION_PH]: PH_COLOR, [CITATION_NO3]: NITRATE_COLOR },
        data: [],
    };

    state = {
        data: [],
        origData: [],
    };

    static getDerivedStateFromProps(nextProps, prevState) {
        if (nextProps.data !== prevState.origData) {
            const data = transformData(nextProps.data);
            return { data, origData: nextProps.data };
        }

        return null;
    }

    render() {
        const { colors } = this.props;
        const { data } = this.state;

        return (
            <ResponsiveContainer width="100%" height={130}>
                <BarChart
                    data={data}
                    margin={{ top: 0, right: 0, left: 0, bottom: 0 }}
                >
                    <CartesianGrid vertical={false} />
                    <XAxis
                        dataKey="day"
                        tick={{
                            fontFamily: 'VerdanaPro-Bold, sans-serif',
                            fill: '#aaa',
                            fontSize: 8,
                            fontWeight: 'bold',
                        }}
                        tickFormatter={v => `${v}`.toUpperCase()}
                        interval={0}
                        tickLine={false}
                    />
                    <Bar
                        dataKey={CITATION_PH}
                        stackId="a"
                        fill={colors[CITATION_PH]}
                    />
                    <Bar
                        dataKey={CITATION_NO3}
                        stackId="a"
                        fill={colors[CITATION_NO3]}
                    />
                </BarChart>
            </ResponsiveContainer>
        );
    }
}
