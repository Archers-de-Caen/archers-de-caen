import React from 'react';

export default function ({ children, ...props }) {
    return (
        <div className="form-groups" {...props}>
            { children }
        </div>
    )
}
