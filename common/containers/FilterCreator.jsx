import React, { Component } from 'react';
import Select from 'antd/lib/select';
import Input from 'antd/lib/input';
import NumberInput from 'antd/lib/input-number';
import DatePicker from 'antd/lib/date-picker';
import trashIcon from '../../../images/icons/merck-icons/trash/trash.svg';
import moment from 'moment';
import {
    analysisParameterValueColumn,
    citationFormValueColumn,
    createdByUserSegmentValueColumn,
    FILTER_CITATION_NAME,
    FILTER_MEASUREMENT_UNIT,
    FILTER_PARAMETER_ANALYSIS,
    FILTER_PARAMETER_NAME,
    FILTER_USER_SEGMENT,
    measurementUnitValueColumn,
    parameterNameValueColumn,
} from '../../analysis-detail/resultListOptions';

const { Option } = Select;

const FILTER = [
    {
        type: 'number',
        operators: [
            {
                name: translations.analysis.filterModal.filters.eq,
                operator: 'eq',
            },
            {
                name: translations.analysis.filterModal.filters.ne,
                operator: 'ne',
            },
            {
                name: translations.analysis.filterModal.filters.gt,
                operator: 'gt',
            },
            {
                name: translations.analysis.filterModal.filters.gte,
                operator: 'gte',
            },
            {
                name: translations.analysis.filterModal.filters.lt,
                operator: 'lt',
            },
            {
                name: translations.analysis.filterModal.filters.lte,
                operator: 'lte',
            },
        ],
    },
    {
        type: 'string',
        operators: [
            {
                name: translations.analysis.filterModal.filters.eq,
                operator: 'eq',
            },
            {
                name: translations.analysis.filterModal.filters.ne,
                operator: 'ne',
            },
            {
                name: translations.analysis.filterModal.filters.li,
                operator: 'li',
            },
        ],
    },
    {
        type: 'date',
        operators: [
            {
                name: translations.analysis.filterModal.filters.gt,
                operator: 'gt',
            },
            {
                name: translations.analysis.filterModal.filters.lt,
                operator: 'lt',
            },
        ],
    },
    {
        type: 'enum',
        operators: [
            {
                name: translations.analysis.filterModal.filters.eq,
                operator: 'eq',
            },
            {
                name: translations.analysis.filterModal.filters.ne,
                operator: 'ne',
            },
        ],
    },
];

function findFilter(type) {
    return FILTER.find(f => f.type === type);
}

function getFirstFilterOperator(type) {
    const filter = findFilter(type);
    if (!filter) {
        return null;
    }

    return filter.operators[0].operator;
}

export const getEnumColumn = selectedParameterName => {
    const column = {
        [FILTER_PARAMETER_NAME]: parameterNameValueColumn,
        [FILTER_CITATION_NAME]: citationFormValueColumn,
        [FILTER_MEASUREMENT_UNIT]: measurementUnitValueColumn,
        [FILTER_USER_SEGMENT]: createdByUserSegmentValueColumn,
        [FILTER_PARAMETER_ANALYSIS]: analysisParameterValueColumn,
    };
    return column[selectedParameterName];
};

export const getEnumValues = selectedParameterName => {
    const enumColumn = getEnumColumn(selectedParameterName);
    return enumColumn.values.map(({ value, label }) => (
        <Option key={value} value={value}>
            {label}
        </Option>
    ));
};

export const formatValue = ({
    filterValue,
    selectedParameter: { type, name },
}) => {
    switch (type) {
        case 'date':
            return moment.unix(filterValue).format('DD.MM.YY, hh:mm a');
        case 'enum':
            const column = getEnumColumn(name);
            if (column && column.values) {
                const enumValue = column.values.find(
                    ({ value }) => value === filterValue,
                );
                if (enumValue && enumValue.label) {
                    return enumValue.label;
                }
            }
            return filterValue;
        default:
            return filterValue;
    }
};

class FilterOption extends Component {
    static defaultProps = {
        selectedParameter: null,
        selectedFilter: null,
        filterValue: null,
        columns: [],
    };

    notifyParent = part => {
        const {
            onChange,
            selectedParameter,
            selectedFilter,
            filterValue,
        } = this.props;

        onChange({
            selectedParameter,
            selectedFilter,
            filterValue,
            ...part,
        });
    };

    handleParameterChange = value => {
        const { columns } = this.props;
        const selectedParameter = columns.find(c => c.name === value);
        const selectedFilter = selectedParameter
            ? getFirstFilterOperator(selectedParameter.type)
            : null;

        const filterValue = (() => {
            if (
                selectedParameter &&
                selectedParameter.type === 'enum' &&
                Array.isArray(selectedParameter.values) &&
                selectedParameter.values[0] &&
                typeof selectedParameter.values[0].value !== 'undefined'
            ) {
                return selectedParameter.values[0].value;
            }
            return null;
        })();

        this.notifyParent({
            selectedParameter,
            selectedFilter,
            filterValue,
        });
    };

    handleFilterChange = value => {
        this.notifyParent({ selectedFilter: value });
    };

    handleFilterValueChange = filterValue => {
        this.notifyParent({ filterValue });
    };

    renderFilter = () => {
        const { selectedParameter, selectedFilter } = this.props;

        const filter = selectedParameter
            ? findFilter(selectedParameter.type)
            : null;

        if (!filter) {
            return <Select disabled />;
        }

        return (
            <Select
                style={{ width: '100%' }}
                value={selectedFilter}
                onChange={this.handleFilterChange}
            >
                {filter.operators.map(o => (
                    <Option key={o.operator} value={o.operator}>
                        {o.name}
                    </Option>
                ))}
            </Select>
        );
    };

    renderFilterValue = () => {
        const { selectedParameter, selectedFilter, filterValue } = this.props;
        if (!selectedParameter || !selectedFilter) {
            return <Input style={{ width: '100%' }} disabled value="" />;
        }

        switch (selectedParameter.type) {
            case 'string':
                return (
                    <Input
                        onChange={e =>
                            this.handleFilterValueChange(e.target.value)
                        }
                        value={filterValue}
                        style={{ width: '100%' }}
                    />
                );
            case 'number':
                return (
                    <NumberInput
                        parser={value => {
                            const n = parseInt(value);
                            if (Number.isNaN(n)) {
                                return '';
                            }
                            return n;
                        }}
                        min={0}
                        onChange={this.handleFilterValueChange}
                        value={filterValue}
                        style={{ width: '100%' }}
                    />
                );
            case 'date':
                const updateDate = date => {
                    if (date) {
                        this.handleFilterValueChange(date.unix());
                    }
                };
                return (
                    <DatePicker
                        style={{ width: '100%' }}
                        showTime
                        defaultValue={
                            filterValue ? moment.unix(filterValue) : moment()
                        }
                        format="YYYY-MM-DD HH:mm"
                        onChange={updateDate}
                        onOk={updateDate}
                    />
                );
            case 'enum':
                return (
                    <Select
                        style={{ width: '100%' }}
                        onChange={this.handleFilterValueChange}
                        value={filterValue}
                    >
                        {getEnumValues(selectedParameter.name)}
                    </Select>
                );
        }
    };

    render() {
        const { columns, onDelete, selectedParameter } = this.props;

        return (
            <div className="row filter-option">
                <div className="col-md-4">
                    <Select
                        style={{ width: '100%' }}
                        onChange={this.handleParameterChange}
                        value={
                            selectedParameter ? selectedParameter.name : null
                        }
                    >
                        {columns.map(column => (
                            <Option key={column.name} value={column.name}>
                                {column.label || column.name}
                            </Option>
                        ))}
                    </Select>
                </div>
                <div className="col-md-3">{this.renderFilter()}</div>
                <div className="col-md-4">{this.renderFilterValue()}</div>
                <div
                    className="col-md-1"
                    style={{ display: 'flex', alignItems: 'center' }}
                >
                    <button
                        onClick={onDelete}
                        style={{
                            display: 'block',
                            backgroundImage: `url(${trashIcon})`,
                            height: 20,
                            width: 20,
                            backgroundSize: 'contain',
                            backgroundRepeat: 'no-repeat',
                            border: 'none',
                            outline: 'none',
                            cursor: 'pointer',
                        }}
                    />
                </div>
            </div>
        );
    }
}

export class FilterCreator extends Component {
    static defaultProps = {
        columns: [],
    };

    state = {
        defaultFilter: {
            selectedParameter: null,
            selectedFilter: null,
            filterValue: null,
        },
    };

    static getDerivedStateFromProps(nextProps) {
        const { columns } = nextProps;
        if (!columns || !columns.length) {
            return null;
        }

        const defaultParameter = columns[0];
        const defaultFilter = defaultParameter
            ? getFirstFilterOperator(defaultParameter.type)
            : null;

        return {
            defaultFilter: {
                selectedParameter: defaultParameter,
                selectedFilter: defaultFilter,
                filterValue: null,
            },
        };
    }

    render() {
        const { onChange, onDelete, filterOptions, columns } = this.props;
        const { defaultFilter } = this.state;

        const options = filterOptions.reduce((acc, filterOption, index) => {
            if (filterOption.hiddenFilter) {
                return acc;
            }

            return [
                ...acc,
                <FilterOption
                    {...defaultFilter}
                    {...filterOption}
                    key={index}
                    onChange={onChange(index)}
                    onDelete={onDelete(index)}
                    columns={columns}
                />,
            ];
        }, []);

        return (
            <div>
                {!options.length ? (
                    <div>
                        {translations.analysis.filterModal.noFiltersApplied}
                    </div>
                ) : (
                    <div className="row">
                        <div className="col-md-4">
                            <h5>
                                {translations.analysis.filterModal.parameter}
                            </h5>
                        </div>
                        <div className="col-md-3">
                            <h5>{translations.analysis.filterModal.filter}</h5>
                        </div>
                        <div className="col-md-4">
                            <h5>{translations.analysis.filterModal.value}</h5>
                        </div>
                    </div>
                )}
                {options}
            </div>
        );
    }
}
