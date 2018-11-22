import React from 'react';
import PropTypes from 'prop-types';
import Spin from 'antd/lib/spin';

export const Loading = ({ loading, children }) =>
    loading ? (
        <div style={{ textAlign: 'center' }}>
            <Spin />
        </div>
    ) : (
        children
    );

Loading.propTypes = {
    loading: PropTypes.bool,
};

Loading.defaultProps = {
    loading: false,
};
