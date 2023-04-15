import React from 'react'
import Field from "@react/components/form/Field";

export default function({ asButton = false, ...props })
{
    return (
        <Field
            type="checkbox"
            invertLabelAndInput={asButton}
            asButton={asButton}
            {...props}
        />
    )
}
