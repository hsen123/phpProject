import React, { Component } from 'react';
import PropTypes from 'prop-types';
import {
    PieChart,
    Pie,
    Legend,
    Tooltip,
    Cell,
    ResponsiveContainer,
} from 'recharts';
import { CITATION_NO3, CITATION_PH } from '../../common/components/Citation';
import { NITRATE_COLOR, PH_COLOR } from '../../result-list/plot/ResultPlot';

export class ActivityPieChart extends Component {
    static propTypes = {
        data: PropTypes.arrayOf(
            PropTypes.shape({
                citationForm: PropTypes.number.isRequired,
                percentage: PropTypes.number.isRequired,
            }),
        ),
        colors: PropTypes.shape({
            [CITATION_PH]: PropTypes.string.isRequired,
            [CITATION_NO3]: PropTypes.string.isRequired,
        }),
    };

    static defaultProps = {
        data: [
            { citationForm: 'ph', percentage: 400 },
            { citationForm: 'no3', percentage: 300 },
        ],
        colors: { [CITATION_PH]: PH_COLOR, [CITATION_NO3]: NITRATE_COLOR },
    };

    render() {
        const { data, colors } = this.props;

        return (
            <ResponsiveContainer width="100%" height={160}>
                <PieChart>
                    <Pie
                        isAnimationActive={false}
                        data={data}
                        dataKey="percentage"
                    >
                        {data.map(entry => (
                            <Cell
                                key={entry.citationForm}
                                fill={colors[entry.citationForm]}
                            />
                        ))}
                    </Pie>
                </PieChart>
            </ResponsiveContainer>
        );
    }
}
