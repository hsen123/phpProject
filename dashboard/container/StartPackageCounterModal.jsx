import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import Form from 'antd/lib/form';
import Input from 'antd/lib/input';
import InputNumber from 'antd/lib/input-number';
import Button from 'antd/lib/button';
import Modal from 'antd/lib/modal';
import bachNumberImage from '../../../images/batch-number.png';

const batchRegex = /^\w{2}\d{6}$/;
function validateBatchNo(batchNumber) {
    if (!batchNumber || batchRegex.test(batchNumber)) {
        return null;
    } else {
        return window.translations.packageCounter.startNew.batch_no_error;
    }
}

export class StartPackageCounterModal extends Component {
    static propTypes = {
        onSubmit: PropTypes.func,
        buttonTitle: PropTypes.node,
        title: PropTypes.node,
        packageCounter: PropTypes.shape({
            batchNumber: PropTypes.string,
            startAmount: PropTypes.number,
        }),
        citationForm: PropTypes.number.isRequired,
    };

    static defaultProps = {
        title: '',
        buttonTitle: window.translations.packageCounter.startNew.action,
        packageCounter: null,
    };

    constructor(props) {
        super(props);

        this.state = {
            loading: false,
            visible: false,
            error: null,
            batchNumber: props.packageCounter
                ? props.packageCounter.batchNumber
                : null,
            startAmount: props.packageCounter
                ? props.packageCounter.startAmount
                : 100,
        };
    }

    showModal = () => {
        this.setState({ visible: true, error: null });
    };

    handleCancel = () => {
        this.setState({
            visible: false,
            batchNumber: this.props.packageCounter
                ? this.props.packageCounter.batchNumber
                : null,
            startAmount: this.props.packageCounter
                ? this.props.packageCounter.startAmount
                : 100,
        });
    };

    handleSubmit = () => {
        const { onSubmit, citationForm } = this.props;
        const { batchNumber, startAmount } = this.state;

        const error = validateBatchNo(batchNumber);
        if (error) {
            this.setState({ error });
            return;
        }

        this.setState({ loading: true, error: null });
        onSubmit({ citationForm, batchNumber, startAmount })
            .then(() => window.location.reload())
            .catch(e => this.setState({ error: e, loading: false }));
    };

    handleInputChange = e => {
        this.setState({ [e.target.name]: e.target.value, error: null });
    };

    handleStartAmountChange = val => {
        this.setState({ startAmount: val });
    };

    render() {
        const { buttonTitle, title, description } = this.props;
        const {
            visible,
            loading,
            batchNumber,
            startAmount,
            error,
        } = this.state;

        let validation = {};
        if (error) {
            validation = {
                validateStatus: 'error',
                help: error,
            };
        }

        return (
            <Fragment>
                <Button
                    type="primary"
                    className="button-reset btn primary button-large"
                    onClick={this.showModal}
                    style={{ margin: '16px 0' }}
                >
                    {buttonTitle}
                </Button>
                <Modal
                    visible={visible}
                    title={<h3 className="merck-font">{title}</h3>}
                    onOk={this.handleSubmit}
                    onCancel={this.handleCancel}
                    okText={window.translations.packageCounter.startNew.submit}
                    cancelText={
                        window.translations.packageCounter.startNew.cancel
                    }
                    confirmLoading={loading}
                >
                    <div className="row">
                        <div className="col-sm-6">
                            <img
                                src={bachNumberImage}
                                alt={
                                    window.translations.packageCounter
                                        .exampleImage
                                }
                                style={{ width: '100%' }}
                            />
                        </div>
                        <div className="col-sm-6">
                            <Form layout={'vertical'}>
                                <Form.Item
                                    {...validation}
                                    label={
                                        window.translations.packageCounter
                                            .startNew.batchNo
                                    }
                                >
                                    <Input
                                        className="ctm-input"
                                        value={batchNumber}
                                        placeholder={description}
                                        onChange={this.handleInputChange}
                                        name="batchNumber"
                                        id="batchNumber"
                                    />
                                </Form.Item>
                                <Form.Item
                                    label={
                                        window.translations.packageCounter
                                            .startNew.strips
                                    }
                                >
                                    <InputNumber
                                        className="ctm-input"
                                        value={startAmount}
                                        onChange={this.handleStartAmountChange}
                                        placeholder={description}
                                        min={0}
                                        max={100}
                                    />
                                </Form.Item>
                            </Form>
                        </div>
                    </div>
                </Modal>
            </Fragment>
        );
    }
}
