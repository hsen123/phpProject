import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { fetchAndHandleAuth } from '../../common/fetch';
import { CITATION_PH } from "../../common/components/Citation";

export class PackageCounterQuery extends Component {
    static propTypes = {
        children: PropTypes.func.isRequired,
        onUpdatePHCount: PropTypes.func,
        onUpdateNO3Count: PropTypes.func,
    };
    static defaultProps = {
        onUpdatePHCount: () => undefined,
        onUpdateNO3Count: () => undefined,
    };

    createPackageCounter = ({
        amountOfTestStripsLeft,
        startAmount,
        batchNumber,
        citationForm,
    }) =>
        fetchAndHandleAuth(`/api/test_strip_packages`, {
            method: 'POST',
            headers: {
                'content-type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                startAmount,
                batchNumber,
                citationForm,
                amountOfTestStripsLeft,
            }),
        }).then(res => res.json());

    adjustPackageCounter = (type, id, delta) =>
        fetchAndHandleAuth(`/api/dashboard/adjustPackageCount`, {
            method: 'POST',
            headers: {
                'content-type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                id,
                delta,
            }),
        })
            .then(res => res.json())
            .then(data => {
                if (data && data.updated) {
                    if (type === CITATION_PH) {
                        const { onUpdatePHCount } = this.props;
                        onUpdatePHCount(delta);
                    } else {
                        const { onUpdateNO3Count } = this.props;
                        onUpdateNO3Count(delta);
                    }
                }
            });
    state = {};
    actions = {
        createPackageCounter: this.createPackageCounter,
        adjustPackageCounter: this.adjustPackageCounter,
    };

    render() {
        return this.props.children(this.state, this.actions);
    }
}
