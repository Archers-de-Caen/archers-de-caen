import React, {PropsWithChildren} from 'react';

interface FormGroupsProps extends PropsWithChildren {
    className?: string,
}

export default function ({ children, className = '', ...props }: FormGroupsProps) {
    return (
        <div className={"form-groups " + className} {...props}>
            { children }
        </div>
    )
}
