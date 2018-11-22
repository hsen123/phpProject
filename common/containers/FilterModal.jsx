import React, { Component } from 'react';
import Button from 'antd/lib/button';
import Modal from 'antd/lib/modal';
import { FilterCreator } from './FilterCreator';
import FilterIcon from '../../../images/icons/material-design/white/filter.svg';

export class FilterModal extends Component {
    state = {
        loading: false,
        visible: false,
        filterOptions: [],
    };

    static getDerivedStateFromProps(nextProps, prevState) {
        if (
            Array.isArray(nextProps.filterOptions) &&
            nextProps.filterOptions !== prevState.filterOptions &&
            !prevState.visible
        ) {
            return { filterOptions: nextProps.filterOptions };
        }
        return null;
    }

    showModal = e => {
        e.preventDefault();
        this.setState({ visible: true });
    };

    handleCancel = () => {
        this.setState({
            visible: false,
            filterOptions: this.props.filterOptions || [],
        });
    };

    handleChange = index => values => {
        this.setState(state => {
            const filterOptions = [...state.filterOptions];
            const option = { ...filterOptions[index], ...values };
            filterOptions.splice(index, 1, option);
            return { filterOptions };
        });
    };

    handleDelete = index => () => {
        this.setState(state => {
            const filterOptions = [...state.filterOptions];
            filterOptions.splice(index, 1);
            return { filterOptions };
        });
    };

    handleSubmit = () => {
        const { onSubmit } = this.props;
        onSubmit(this.state.filterOptions);
        this.setState({ visible: false });
    };

    handleAddFilter = () => {
        this.setState(state => ({
            filterOptions: [...state.filterOptions, {}],
        }));
    };

    render() {
        const { visible, loading, filterOptions } = this.state;
        const { columns } = this.props;

        return (
            <React.Fragment>
                <Button type="primary" onClick={this.showModal}>
                    <img
                        src={FilterIcon}
                        alt="Filter"
                        className="button-icon"
                    />
                    {translations.analysis.filter}
                </Button>
                <Modal
                    width={800}
                    visible={visible}
                    title={
                        <h3 className="merck-font">
                            {translations.analysis.filter}
                        </h3>
                    }
                    onOk={this.handleSubmit}
                    onCancel={this.handleCancel}
                    footer={[
                        <Button key="back" onClick={this.handleCancel}>
                            {translations.analysis.modal.cancel}
                        </Button>,
                        <Button key="newFilter" onClick={this.handleAddFilter}>
                            {translations.analysis.filterModal.addFilter}
                        </Button>,
                        <Button
                            key="submit"
                            type="primary"
                            loading={loading}
                            onClick={this.handleSubmit}
                        >
                            {translations.analysis.filterModal.apply}
                        </Button>,
                    ]}
                >
                    <FilterCreator
                        filterOptions={filterOptions}
                        onChange={this.handleChange}
                        onDelete={this.handleDelete}
                        columns={columns}
                    />
                </Modal>
            </React.Fragment>
        );
    }
}
