import React from 'react'
import {Field} from "formik"
import FormGroup from "@react/components/form/FormGroup"

export default function({
    name = '',
    id = '',
    type = 'text',
    required = false,
    errors = null,
    ...props
}) {
    const {
        children,
        asButton = false,
        invertLabelAndInput = false,
        useFormik = false,
        validate = null,
        ...propsFiltered
    } = props

    if (validate && !useFormik) {
        throw new Error('La propriété "validate" doit être utiliser avec formik')
    }

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
            validate={validate}
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

    let className = ''

    if (['radio', 'checkbox'].includes(type)) {
        className += ' --check'
    }

    if (asButton) {
        className += ' --btn'
    }

    if (errors) {
        className += ' -error'
    }

    return (
        <FormGroup className={className}>
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

            { errors && <div className="errors">{ errors }</div> }
        </FormGroup>
    )
}
