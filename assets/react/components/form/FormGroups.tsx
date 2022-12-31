import React, { PropsWithChildren } from 'react';

export default function ({ children }: PropsWithChildren) {
    return (
        <div className="form-groups">
            { children }
        </div>
    )
}
