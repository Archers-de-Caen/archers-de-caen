import React, { PropsWithChildren } from 'react';

interface FormGroupProps extends PropsWithChildren {
    className?: string
}

export default function ({ children, className = '', ...props }: FormGroupProps) {
    return (
        <div className={"form-group" + ' ' + className} {...props}>
            { children }
        </div>
    )
}
