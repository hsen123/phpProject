import React, { Component } from 'react';
import { columns, filterColumns } from '../../analysis/analysisTableOptions';
import Button from 'antd/lib/button';
import '../../../css/analyses.less';
import { FilterModal } from '../../common/containers/FilterModal';
import { AnalysisQuery } from '../../analysis/containers/AnalysisQuery';
import { DataTable } from '../../common/components/DataTable';
import CSVIcon from '../../../images/icons/material-design/white/csv.svg';
import ExcelIcon from '../../../images/icons/material-design/white/excel.svg';
import ZipIcon from '../../../images/icons/material-design/white/zip.svg';
import ZipIconGray from '../../../images/icons/material-design/gray/zip.svg';
import { ManageColumnsModal } from '../../common/containers/ManageColumnsModal';

const adminColumns = columns.map(item => {
    if (item.key === 'name') {
        return {
            ...item,
            render: (name) => (
                <span className="merck-font name">{name}</span>
            ),
        };
    }

    if (item.key === 'userName') {
        return {
            ...item,
            render: (name, row) => (
                <a
                    style={{ color: '#503291', fontSize: '14px' }}
                    href={`/profile/${row.user.id}`}
                >
                    {!name ? row.email : name}
                </a>
            ),
        };
    }

    return item;
});

export class AnalysisAdminPage extends Component {
    static defaultProps = {};

    renderButtonBar = ({
        onFilterChange,
        createAnalyse,
        deleteAnalyses,
        onDeleteSelection,
        rowSelection: { selectedRowKeys },
        filters,
        getExportLink,
        disabledColumns,
        onMetaDataSubmit,
    }) => (
        <Button.Group className="datatable-button-bar">
            <div>
                <FilterModal
                    onSubmit={onFilterChange}
                    filterOptions={filters}
                    columns={filterColumns}
                />
                <Button
                    href={getExportLink({
                        format: 'csv',
                        ids: selectedRowKeys,
                    })}
                    target="_blank"
                    rel="noopener noreferrer"
                    type="primary"
                >
                    <img
                        src={CSVIcon}
                        alt={window.translations.resultList.csv}
                        className="button-icon"
                    />
                    {window.translations.resultList.csv}
                </Button>
                <Button
                    href={getExportLink({
                        format: 'xlsx',
                        ids: selectedRowKeys,
                    })}
                    target="_blank"
                    rel="noopener noreferrer"
                    type="primary"
                >
                    <img
                        src={ExcelIcon}
                        alt={window.translations.resultList.excel}
                        className="button-icon"
                    />
                    {window.translations.resultList.excel}
                </Button>
                <Button
                    href={getExportLink({
                        format: 'zip',
                        ids: selectedRowKeys,
                    })}
                    target="_blank"
                    rel="noopener noreferrer"
                    type="primary"
                    disabled={!selectedRowKeys.length}
                >
                    <img
                        src={!selectedRowKeys.length ? ZipIconGray : ZipIcon}
                        alt={window.translations.resultList.zip}
                        className="button-icon"
                    />
                    {window.translations.resultList.zip}
                </Button>
            </div>
            <ManageColumnsModal
                columns={columns}
                disabledColumns={disabledColumns}
                onSubmit={onMetaDataSubmit}
            />
        </Button.Group>
    );

    render() {
        return (
            <div>
                <AnalysisQuery>
                    {(
                        {
                            data,
                            pagination,
                            sorter,
                            error,
                            loading,
                            selectedRowKeys,
                            ...restProps
                        },
                        {
                            onTableChange,
                            onSelectChange,
                            onPageChange,
                            onSearch,
                            onFilterChange,
                            removeFilter,
                            ...restActions
                        },
                    ) => (
                        <DataTable
                            columns={adminColumns}
                            dataSource={data}
                            loading={loading}
                            rowKey="id"
                            rowSelection={{
                                selectedRowKeys,
                                onChange: onSelectChange,
                            }}
                            pagination={pagination}
                            onChange={onTableChange}
                            renderButtonBar={this.renderButtonBar}
                            onPageChange={onPageChange}
                            onSearch={onSearch}
                            onFilterChange={onFilterChange}
                            removeFilter={removeFilter}
                            {...restProps}
                            {...restActions}
                        />
                    )}
                </AnalysisQuery>
            </div>
        );
    }
}
