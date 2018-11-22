import React, { Component, Fragment } from 'react';
import PT from 'prop-types';
import Button from 'antd/lib/button';
import Modal from 'antd/lib/modal';
import DeleteIcon from '../../../images/icons/material-design/white/trash.svg';
import DeleteIconGray from '../../../images/icons/material-design/gray/trash.svg';
import { DEFAULT_MODAL_WIDTH } from '../constants';
import { Loading } from '../components/Loading';

export class DeleteModal extends Component {
    static propTypes = {
        onDeleteSelection: PT.func.isRequired,
        isDeletable: PT.bool.isRequired,
        title: PT.string,
        description: PT.oneOfType([PT.string, PT.element]),
    };

    static defaultProps = {
        renderButton: ({ isDeletable, toggleModal }) => (
            <Button
                type="primary"
                disabled={!isDeletable}
                onClick={() => toggleModal(true)}
            >
                <img
                    src={isDeletable ? DeleteIcon : DeleteIconGray}
                    alt="Delete"
                    className="button-icon"
                />
                {window.translations.analysis.delete}
            </Button>
        ),
        title: window.translations.analysis.deleteModal.title,
        description: window.translations.analysis.deleteModal.description,
    };

    state = { loading: false, visible: false };

    toggleModal = force => {
        this.setState(({ visible }) => ({
            visible: force !== undefined ? !!force : !visible,
        }));
    };

    handleCancel = () => {
        this.setState({ visible: false });
    };

    handleDelete = () => {
        const { onDeleteSelection } = this.props;
        this.setState({ loading: true });
        onDeleteSelection().finally(() =>
            this.setState({ loading: false, visible: false }),
        );
    };

    actions = { toggleModal: this.toggleModal };

    render() {
        const {
            isDeletable,
            renderButton,
            title,
            description,
            loading: analysisLoading,
        } = this.props;
        const { visible, loading } = this.state;

        return (
            <Fragment>
                {renderButton({ isDeletable, ...this.actions })}
                <Modal
                    width={DEFAULT_MODAL_WIDTH}
                    visible={visible}
                    title={
                        <Loading loading={analysisLoading}>
                            <h3 className="merck-font">{title}</h3>
                        </Loading>
                    }
                    onCancel={this.handleCancel}
                    footer={[
                        <Button key="back" onClick={this.handleCancel}>
                            {window.translations.analysis.modal.cancel}
                        </Button>,
                        <Button
                            key="submit"
                            type="primary"
                            loading={loading}
                            onClick={this.handleDelete}
                        >
                            {window.translations.analysis.delete}
                        </Button>,
                    ]}
                >
                    {description}
                </Modal>
            </Fragment>
        );
    }
}
