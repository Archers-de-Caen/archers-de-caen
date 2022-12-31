import React from 'react'
import {Field} from "formik"
import FormGroup from "@react/components/form/FormGroup"

export default function({ children, name, type = 'text', required = false, ...props })
{
    return (
        <FormGroup>
            <label
                htmlFor={name}
                className={required ? "required" : ""}
            >
                {children}
            </label>
            <Field
                type={type}
                id={name}
                name={name}
                required={required}
                {...props}
            />
        </FormGroup>
    )
}
