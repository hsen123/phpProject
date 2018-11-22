import React from 'react';
import PropTypes from 'prop-types';
import { Citation } from '../../common/components/Citation';
import { replaceWithElement } from '../../common/helper';
import Button from 'antd/es/button/button';
import plusIcon from '../../../images/icons/material-design/dark-gray/plus.svg';
import minusIcon from '../../../images/icons/material-design/dark-gray/minus.svg';
export const PackageCounterInfo = ({
    strokeColor,
    packageCounter,
    adjustPackageCounter,
    type,
}) => {
    const amount = packageCounter.results.length;
    const startAmount = packageCounter.startAmount;
    const amountOfTestStripsLeft = packageCounter.amountOfTestStripsLeft;
    const batchNumber = window.translations.packageCounter.batchNo.replace(
        '%number%',
        packageCounter.batchNumber || '-',
    );

    const stripsUsed = replaceWithElement(
        window.translations.packageCounter.stripsUsed,
        '%count%',
        <Citation constant={packageCounter.citationForm} />,
    );

    return (
        <div
            className="row"
            style={{
                paddingBottom: '16px',
                display: 'flex',
                alignItems: 'center',
            }}
        >
            <div className="col-sm-6 counter-column">
                <span
                    className="merck-font"
                    style={{
                        color: strokeColor,
                        fontSize: 24,
                        lineHeight: '18px',
                    }}
                >
                    {amount}
                </span>
                <span
                    className="merck-font"
                    style={{
                        color: 'rgb(95, 95, 95)',
                        fontSize: 24,
                        lineHeight: '18px',
                    }}
                >
                    {`/${startAmount}`}
                </span>
                <div className="count-buttons">
                    <Button
                        type="ghost"
                        disabled={packageCounter.startAmount === 100}
                        onClick={() =>
                            adjustPackageCounter(type, packageCounter.id, 1)
                        }
                    >
                        <img src={plusIcon} />
                    </Button>
                    <Button
                        type="ghost"
                        disabled={packageCounter.amountOfTestStripsLeft === 0}
                        onClick={() =>
                            adjustPackageCounter(type, packageCounter.id, -1)
                        }
                    >
                        <img src={minusIcon} />
                    </Button>
                </div>
            </div>
            <div className="col-sm-6 used-column">
                <div>{stripsUsed}</div>
                <div style={{ lineHeight: '10px' }}>
                    <span className="text-muted">
                        <small>{batchNumber}</small>
                    </span>
                </div>
            </div>
        </div>
    );
};

PackageCounterInfo.propTypes = {
    strokeColor: PropTypes.string,
    packageCounter: PropTypes.shape({
        results: PropTypes.array,
        batchNumber: PropTypes.node,
        citationForm: PropTypes.number,
    }),
};
PackageCounterInfo.defaultProps = {};
