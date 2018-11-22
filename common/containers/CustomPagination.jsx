import React, { Component, Fragment } from 'react';
import Select from 'antd/lib/select';
import Input from 'antd/lib/input';
import PaginationNextImage from '../../../images/icons/table/right.svg';
import PaginationPrevImage from '../../../images/icons/table/left.svg';

const { Option } = Select;

const styles = {
    pageSizeSelect: {
        width: '5.1em',
        marginRight: '8px',
    },
    countLabel: {
        marginRight: '8px',
    },
    navigationNext: {
        background: `url(${PaginationNextImage})`,
        width: '20px',
        height: '20px',
    },
    navigationPrev: {
        background: `url(${PaginationPrevImage})`,
        width: '20px',
        height: '20px',
    },
};

export class CustomPagination extends Component {
    static propTypes = {};
    static defaultProps = {
        fixedPageSize: false,
        pageSize: 20,
        current: 1,
        total: 0,
    };

    state = {
        current: 1,
        origCurrent: 1,
    };

    static getDerivedStateFromProps(nextProps, prevState) {
        if (
            Number(nextProps.current) !== Number(prevState.origCurrent) ||
            nextProps.total !== prevState.total ||
            nextProps.pageSize !== prevState.pageSize
        ) {
            return {
                current: nextProps.current,
                origCurrent: nextProps.current,
                total: nextProps.total,
                pageSize: nextProps.pageSize,
                start:
                    nextProps.total > 0
                        ? Math.max(
                              (nextProps.current - 1) * nextProps.pageSize + 1,
                              1,
                          )
                        : 0,
                end: Math.min(
                    nextProps.current * nextProps.pageSize,
                    nextProps.total,
                ),
                nextDisabled:
                    nextProps.current >= nextProps.total / nextProps.pageSize,
                prevDisabled: nextProps.current <= 1,
            };
        }

        return null;
    }

    handlePageSizeChange = pageSize => {
        const { onShowSizeChange, current } = this.props;
        onShowSizeChange(current, pageSize);
    };

    handlePageChange = current => {
        const { onChange, pageSize } = this.props;
        onChange(current, pageSize);
    };

    createPageChanger = value => () => {
        const { current } = this.props;
        this.handlePageChange(current + value);
    };

    handlePageManualChange = e => {
        this.setState({ current: e.target.value });
    };

    handlePageManualSave = e => {
        const { pageSize, total, current: resetToCurrent } = this.props;
        let page = parseInt(e.target.value);
        if (!Number.isNaN(page) && page !== resetToCurrent) {
            const maxPage = Math.ceil(total / pageSize);
            if (page > maxPage) {
                page = maxPage;
            }

            if (page < 1) {
                page = 1;
            }
            this.setState({ current: page }, () => this.handlePageChange(page));
            return;
        }

        this.setState({ current: resetToCurrent });
    };

    render() {
        const { pageSize, total } = this.props;
        const {
            current: stateCurrent,
            start,
            end,
            nextDisabled,
            prevDisabled,
        } = this.state;

        return (
            <Fragment>
                <span key="count" style={styles.countLabel}>
                    {start}-{end} of {total}
                </span>
                <div
                    style={{
                        display: 'flex',
                        marginRight: '8px',
                        alignItems: 'center',
                    }}
                >
                    <button
                        className="button-reset"
                        style={{
                            ...styles.navigationPrev,
                            cursor: prevDisabled ? 'not-allowed' : 'pointer',
                        }}
                        onClick={this.createPageChanger(-1)}
                        disabled={prevDisabled}
                    />
                    <Input
                        value={stateCurrent}
                        onChange={this.handlePageManualChange}
                        onBlur={this.handlePageManualSave}
                        onPressEnter={this.handlePageManualSave}
                        style={{
                            width: '3em',
                            textAlign: 'center',
                            border: 'none',
                            borderBottom: '1px solid #503291',
                        }}
                    />
                    <button
                        className="button-reset"
                        style={{
                            ...styles.navigationNext,
                            cursor: nextDisabled ? 'not-allowed' : 'pointer',
                        }}
                        onClick={this.createPageChanger(1)}
                        disabled={nextDisabled}
                    />
                </div>
                {!this.props.fixedPageSize && (
                    <Select
                        key="select"
                        value={pageSize}
                        style={styles.pageSizeSelect}
                        onChange={this.handlePageSizeChange}
                    >
                        {[10, 20, 50, 100].map(items => (
                            <Option key={items} value={items}>
                                {items}
                            </Option>
                        ))}
                    </Select>
                )}
            </Fragment>
        );
    }
}
