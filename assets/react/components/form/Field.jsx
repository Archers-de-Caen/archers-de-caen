import React from 'react'
import {Field} from "formik"
import FormGroup from "@react/components/form/FormGroup"

export default function({
    name = '',
    id = '',
    type = 'text',
    required = false,
    ...props
}) {
    const {
        children,
        asButton = false,
        invertLabelAndInput = false,
        useFormik = false,
        ...propsFiltered
    } = props

    const label = (
        <label
            htmlFor={id ? id : name}
            className={required ? "required" : ""}
        >
            {children}
        </label>
    )

    const input = useFormik ? (
        <Field
            type={type}
            id={id ? id : name}
            name={name}
            required={required}
            {...propsFiltered}
        />
    ) : (
        <input
            type={type}
            id={id ? id : name}
            name={name}
            required={required}
            {...propsFiltered}
        />
    )

    return (
        <FormGroup className={(['radio', 'checkbox'].includes(type) ? ' --check' : '') + (asButton ? ' --btn' : '')}>
            {
                !invertLabelAndInput ? (
                    <>
                        { label }
                        { input }
                    </>
                ) : (
                    <>
                        { input }
                        { label }
                    </>
                )
            }
        </FormGroup>
    )
}
