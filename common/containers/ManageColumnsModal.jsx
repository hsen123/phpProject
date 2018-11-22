import React, { Component, Fragment } from 'react';
import Button from 'antd/lib/button';
import Modal from 'antd/lib/modal';
import Checkbox from 'antd/lib/checkbox';
import ManageColumnsIcon from '../../../images/icons/material-design/white/columns.svg';

export class ManageColumnsModal extends Component {
    static defaultProps = {
        disabledColumns: [],
        columns: [],
    };

    constructor(props, ...restArgs) {
        super(props, ...restArgs);
        const { disabledColumns, columns } = props;

        this.state = {
            loading: false,
            visible: false,
            columns: ManageColumnsModal.transformColumns(
                columns,
                disabledColumns,
            ),
            disabledColumns: disabledColumns,
        };
    }

    static getDerivedStateFromProps(nextProps, prevState) {
        if (nextProps.disabledColumns === prevState.disabledColumns) {
            return null;
        }

        return {
            disabledColumns: nextProps.disabledColumns,
            columns: ManageColumnsModal.transformColumns(
                nextProps.columns,
                nextProps.disabledColumns,
            ),
        };
    }

    static transformColumns = (columns, disabledColumns) =>
        columns.map(c => ({
            ...c,
            active: !disabledColumns.find(({ key }) => key === c.key),
        }));

    showModal = () => {
        this.setState({ visible: true });
    };

    handleCancel = () => {
        const { columns, disabledColumns } = this.props;

        this.setState({
            visible: false,
            columns: ManageColumnsModal.transformColumns(
                columns,
                disabledColumns,
            ),
            disabledColumns,
        });
    };

    handleToggle = ({ target: { keyIdent, checked } }) => {
        this.setState(state => {
            const columns = state.columns.reduce((columns, c) => {
                if (c.key === keyIdent) {
                    return [...columns, { ...c, active: checked }];
                }

                return [...columns, c];
            }, []);
            return { columns };
        });
    };

    handleSubmit = () => {
        const { onSubmit } = this.props;
        onSubmit(this.state.columns);
        this.setState({ visible: false });
    };

    render() {
        const { visible, loading, columns } = this.state;

        return (
            <Fragment>
                <Button type="primary" onClick={this.showModal}>
                    <img
                        src={ManageColumnsIcon}
                        alt="Manage columns"
                        className="button-icon"
                    />
                    {translations.analysisDetail.manageColumns}
                </Button>
                <Modal
                    width={400}
                    visible={visible}
                    title={
                        <h3 className="merck-font">
                            {translations.analysisDetail.manageColumns}
                        </h3>
                    }
                    onOk={this.handleSubmit}
                    onCancel={this.handleCancel}
                    footer={[
                        <Button key="back" onClick={this.handleCancel}>
                            {translations.analysisDetail.modal.cancel}
                        </Button>,
                        <Button
                            key="submit"
                            type="primary"
                            loading={loading}
                            onClick={this.handleSubmit}
                        >
                            {translations.analysisDetail.modal.save}
                        </Button>,
                    ]}
                >
                    {columns.map(({ key, active, title }) => (
                        <div key={key}>
                            <Checkbox
                                onChange={this.handleToggle}
                                checked={active}
                                keyIdent={key}
                            >
                                {title}
                            </Checkbox>
                        </div>
                    ))}
                </Modal>
            </Fragment>
        );
    }
}
