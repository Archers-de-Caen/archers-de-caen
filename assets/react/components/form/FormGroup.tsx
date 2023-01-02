import React, { PropsWithChildren } from 'react';

interface FormGroupProps extends PropsWithChildren {
    check?: boolean
    btn?: boolean
    className?: string
}

export default function ({ children, check = false, btn = false, className = '' }: FormGroupProps) {
    return (
        <div className={"form-group" + (check ? ' --check' : '') + (btn ? ' --btn' : '') + ' ' + className}>
            { children }
        </div>
    )
}
