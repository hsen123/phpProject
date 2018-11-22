import React from 'react';

export const ErrorAlert = ({ children }) => (
    <div className="alert alert-danger" role="alert">
        {children}
    </div>
);
