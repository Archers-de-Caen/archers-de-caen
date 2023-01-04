import React, { PropsWithChildren } from 'react';

interface FormGroupProps extends PropsWithChildren {
    check?: boolean
    asButton?: boolean
    className?: string
}

export default function ({ children, className = '' }: FormGroupProps) {
    return (
        <div className={"form-group" + ' ' + className}>
            { children }
        </div>
    )
}
