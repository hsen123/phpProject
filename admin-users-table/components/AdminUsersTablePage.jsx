import React, { Component } from 'react';
import { UsersQuery } from '../containers/UsersQuery';
import { FilterModal } from '../../common/containers/FilterModal';
import { columns, filterColumns } from '../usersTableOptions';
import { DataTable } from '../../common/components/DataTable';
import CSVIcon from '../../../images/icons/material-design/white/csv.svg';
import ExcelIcon from '../../../images/icons/material-design/white/excel.svg';
import Button from 'antd/lib/button';
import '../../../css/users-table.less';
import { ManageColumnsModal } from '../../common/containers/ManageColumnsModal';

export class AdminUsersTablePage extends Component {
    renderButtonBar = ({
        onFilterChange,
        filters,
        getExportLink,
        disabledColumns,
        onMetaDataSubmit,
        rowSelection: { selectedRowKeys },
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
                <UsersQuery>
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
                            columns={columns}
                            dataSource={data}
                            loading={loading}
                            rowKey="id"
                            rowSelection={{
                                selectedRowKeys,
                                onChange: onSelectChange,
                            }}
                            renderButtonBar={this.renderButtonBar}
                            pagination={pagination}
                            onChange={onTableChange}
                            onPageChange={onPageChange}
                            onSearch={onSearch}
                            onFilterChange={onFilterChange}
                            removeFilter={removeFilter}
                            {...restProps}
                            {...restActions}
                        />
                    )}
                </UsersQuery>
            </div>
        );
    }
}
