import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import Table from 'antd/lib/table';
import Input from 'antd/lib/input';
import Tooltip from 'antd/lib/tooltip';
import Tag from 'antd/lib/tag';
import { CustomPagination } from '../containers/CustomPagination';
import { formatValue } from '../containers/FilterCreator';

const { Search } = Input;
const primaryColor = '#503291';

export class DataTable extends Component {
    static propTypes = {
        disabledColumns: PropTypes.array,
        columns: PropTypes.array,
        dataSource: PropTypes.array,
        renderSearch: PropTypes.bool,
        renderFilterTags: PropTypes.bool,
    };

    static defaultProps = {
        disabledColumns: [],
        columns: [],
        filters: [],
        dataSource: [],
        renderSearch: true,
        renderFilterTags: true,
    };
    state = {
        columns: [],
        disabledColumns: [],
    };
    renderPaginationBar = ({
        pagination,
        search,
        onSearch,
        onSearchChange,
        filters,
        removeFilter,
        renderFilterTags,
        renderSearch,
    }) => {
        return (
            <Fragment>
                <div
                    className="row no-gutters"
                    style={{
                        display: 'flex',
                        flex: 1,
                        alignItems: 'center',
                        flexWrap: 'wrap',
                    }}
                >
                    {renderFilterTags &&
                        filters
                            .filter(v => Object.keys(v).length)
                            .map((filter, index) => {
                                if (!filter.hiddenFilter) {
                                    const tagContent = (
                                        <Fragment>
                                            {filter.selectedParameter.label
                                                ? filter.selectedParameter.label
                                                : filter.selectedParameter.name}
                                            {` ${filter.selectedFilter} `}
                                            {formatValue(filter)}
                                        </Fragment>
                                    );

                                    const longTag = (
                                        <Fragment>
                                            {filter.selectedParameter.label
                                                ? filter.selectedParameter.label
                                                : filter.selectedParameter.name}
                                            {` ${window.translations
                                                .analysisDetail.filterModal
                                                .filters[
                                                filter.selectedFilter
                                            ] || filter.selectedFilter} `}
                                            {formatValue(filter)}
                                        </Fragment>
                                    );

                                    return (
                                        <Tooltip
                                            title={longTag}
                                            key={`${
                                                filter.selectedParameter.name
                                            }-${index}`}
                                        >
                                            <Tag
                                                style={{
                                                    marginTop: 5,
                                                    marginBottom: 1,
                                                }}
                                                color={primaryColor}
                                                key={`${
                                                    filter.selectedParameter
                                                        .name
                                                }-tag`}
                                                afterClose={() =>
                                                    removeFilter(filter)
                                                }
                                                closable
                                            >
                                                <span className="max-115">
                                                    {tagContent}
                                                </span>
                                            </Tag>
                                        </Tooltip>
                                    );
                                }
                            })}
                </div>
                <CustomPagination {...pagination} />
                {renderSearch && (
                    <Search
                        placeholder="Search"
                        onSearch={onSearch}
                        onChange={onSearchChange}
                        value={search}
                        style={{ width: 200 }}
                    />
                )}
            </Fragment>
        );
    };
    calcScrollX = () => {
        const { columns, disabledColumns } = this.props;

        const max = columns.length;
        const actual = max - disabledColumns.length;
        const sizeOfOne = 170;

        return actual * sizeOfOne;
    };

    static getDerivedStateFromProps(nextProps, prevState) {
        if (!nextProps.disabledColumns) {
            return { columns: nextProps.columns };
        }

        if (nextProps.disabledColumns !== prevState.disabledColumns) {
            const disabledColumnKeys = nextProps.disabledColumns.map(
                ({ key }) => key,
            );
            return {
                disabledColumns: nextProps.disabledColumns,
                columns: nextProps.columns.filter(
                    c => !disabledColumnKeys.includes(c.key),
                ),
            };
        }

        return null;
    }

    render() {
        const {
            dataSource,
            renderPaginationBar,
            renderButtonBar,
            sorter,
            ...restProps
        } = this.props;

        const { columns } = this.state;

        return (
            <Fragment>
                <div className="row no-gutters">
                    <div className="col-sm-12">
                        <div className="datatable-control-bar">
                            {renderPaginationBar
                                ? renderPaginationBar(this.props, {
                                      renderPaginationBar: this
                                          .renderPaginationBar,
                                  })
                                : this.renderPaginationBar(this.props)}
                        </div>
                    </div>
                </div>
                {renderButtonBar ? (
                    <div className="row no-gutters">
                        <div className="col-sm-12">
                            <div>{renderButtonBar(this.props)}</div>
                        </div>
                    </div>
                ) : null}
                <div className="row no-gutters">
                    <div className="col-sm-12">
                        <div>
                            <Table
                                key="table"
                                scroll={{ x: this.calcScrollX() }}
                                {...restProps}
                                pagination={false}
                                columns={columns}
                                dataSource={dataSource}
                                size="middle"
                            />
                        </div>
                    </div>
                </div>
            </Fragment>
        );
    }
}
