import React, { PropsWithChildren } from 'react';

interface FormGroupProps extends PropsWithChildren {
    check?: boolean
}

export default function ({ children, check }: FormGroupProps) {
    return (
        <div className={"form-group" + (check ? ' --check' : '')}>
            { children }
        </div>
    )
}
