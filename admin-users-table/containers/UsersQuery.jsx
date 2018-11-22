import React, { Component } from 'react';
import {
    combineQueryStrings,
    encodeIds,
    getQueryString,
} from '../../common/getQueryString';
import { fetchAndHandleAuth } from '../../common/fetch';
import { transformData } from '../usersTableOptions';

function excludeKeys(keys) {
    return target =>
        Object.entries(target).reduce((acc, [key, value]) => {
            if (keys.includes(key)) {
                return acc;
            }
            return {
                ...acc,
                [key]: value,
            };
        }, {});
}

function transformMetaData(metaData, state) {
    if (metaData && typeof metaData === 'object' && !Array.isArray(metaData)) {
        const {
            disabledColumns,
            itemsPerPage,
            page,
            order,
            filters,
            search,
        } = metaData;

        if (
            Array.isArray(disabledColumns) &&
            Number.isFinite(itemsPerPage) &&
            Number.isFinite(page) &&
            order &&
            typeof order === 'object' &&
            (typeof order.field === 'string' ||
                typeof order.field === 'undefined') &&
            (typeof order.direction === 'string' ||
                typeof order.direction === 'undefined') &&
            Array.isArray(filters) &&
            typeof search === 'string'
        ) {
            return {
                disabledColumns,
                pagination: {
                    ...state.pagination,
                    itemsPerPage,
                    current: page,
                },
                order,
                filters,
                search,
            };
        }
    }

    return null;
}

export class UsersQuery extends Component {
    static defaultProps = {
        basePath: '/api/users',
    };

    getDefaultParams = () => ({
        itemsPerPage: this.state.pagination.pageSize,
        page: this.state.pagination.current,
        order: {
            field: this.state.sorter.field,
            direction: this.state.sorter.order,
        },
        filters: this.state.filters,
        search: this.state.search,
    });

    fetch = (params = this.getDefaultParams()) => {
        this.setState({ loading: true });
        return this.fetchForTable(params)
            .then(res => {
                this.setState({ loading: false });
                this.handleMetaDataSubmit(this.state.disabledColumns);
                return res;
            })
            .catch(e => this.setState({ loading: false, error: e }));
    };

    fetchForTable = params => {
        this.setState({ tableLoading: true });
        return fetchAndHandleAuth(
            `${this.props.basePath}?${getQueryString(params)}`,
            {
                headers: {
                    accept: 'application/ld+json',
                },
                credentials: 'include',
            },
        )
            .then(res => res.json())
            .then(raw => {
                const data = transformData(raw);
                const pagination = {
                    ...this.state.pagination,
                    total: raw['hydra:totalItems'],
                };
                this.setState({ tableLoading: false, data, pagination });
                return data;
            })
            .catch(e => {
                this.setState({ tableLoading: false, error: e.message });
            });
    };

    getExportLink = ({ ids = [], format = 'csv', withFilters = true }) => {
        const timezoneOffset = -new Date().getTimezoneOffset();
        return `${
            this.props.basePath
        }.${format}?pagination=false&tzo=${timezoneOffset}&${combineQueryStrings(
            withFilters &&
                getQueryString(
                    excludeKeys(['page', 'itemsPerPage'])(
                        this.getDefaultParams(),
                    ),
                ),
            ids.length && encodeIds(ids),
        )}`;
    };

    handlePageChange = (current, pageSize) => {
        this.setState(
            state => ({
                pagination: {
                    ...state.pagination,
                    current:
                        state.pagination.pageSize !== pageSize ? 1 : current,
                    pageSize,
                },
            }),
            () => {
                this.fetch();
            },
        );
    };

    handleTableChange = (pagination, filters, sorter) => {
        this.setState(
            state => ({
                selectedRowKeys: [],
                pagination: {
                    ...state.pagination,
                    current: pagination.current,
                },
                sorter,
            }),
            () => {
                this.fetch();
            },
        );
    };

    handleSearch = search => {
        this.setState(
            state => ({
                selectedRowKeys: [],
                pagination: {
                    ...state.pagination,
                    current: 1,
                },
                search,
            }),
            () => {
                this.fetch();
            },
        );
    };

    removeFilter = filter => {
        this.setState(
            state => ({
                selectedRowKeys: [],
                filters: state.filters.filter(
                    f =>
                        f.filterValue !== filter.filterValue &&
                        f.selectedFilter !== filter.selectedFilter &&
                        f.selectedParameter !== filter.selectedParameter,
                ),
            }),
            () => this.fetch(),
        );
    };

    handleFilterChange = filters => {
        this.setState({ filters, selectedRowKeys: [] }, () => {
            this.fetch();
        });
    };

    handleSelectChange = selectedRowKeys => {
        this.setState({ selectedRowKeys });
    };

    handleMetaDataSubmit = columns => {
        const disabledColumns = columns
            .filter(c => !c.active)
            .map(({ key }) => ({ key }));

        const metadata = {
            disabledColumns,
            itemsPerPage: this.state.pagination.pageSize,
            page: this.state.pagination.current,
            order: {
                field: this.state.sorter.field,
                direction: this.state.sorter.order,
            },
            filters: this.state.filters,
            search: this.state.search,
        };

        this.setState({ disabledColumns });

        return fetchAndHandleAuth('/profile/metadata/user', {
            credentials: 'include',
            method: 'POST',
            headers: {
                'content-type': 'application/json',
            },
            body: JSON.stringify(metadata),
        }).catch(e => {
            this.setState({ error: e.message });
        });
    };

    applyMetaDataToState = () =>
        new Promise(resolve =>
            this.setState(
                state => transformMetaData(window.userListMeta, state),
                resolve,
            ),
        );

    actions = {
        fetch: this.fetch,
        onDeleteSelection: this.deleteResult,
        onTableChange: this.handleTableChange,
        onSelectChange: this.handleSelectChange,
        onPageChange: this.handlePageChange,
        onSearch: this.handleSearch,
        onFilterChange: this.handleFilterChange,
        removeFilter: this.removeFilter,
        getExportLink: this.getExportLink,
        onMetaDataSubmit: this.handleMetaDataSubmit,
    };

    constructor(...args) {
        super(...args);
        this.state = {
            data: null,
            pagination: {
                current: 1,
                pageSize: 20,
                onShowSizeChange: this.handlePageChange,
                onChange: this.handlePageChange,
            },
            sorter: {},
            filters: [],
            disabledColumns: [],
            search: '',
            error: null,
            visualError: null,
            tableError: null,
            loading: true,
            tableLoading: true,
            exportLoading: false,
            visualLoading: true,
            selectedRowKeys: [],
        };
    }

    componentDidMount() {
        this.applyMetaDataToState().then(() => this.fetch());
    }

    render() {
        return this.props.children(this.state, this.actions);
    }
}
