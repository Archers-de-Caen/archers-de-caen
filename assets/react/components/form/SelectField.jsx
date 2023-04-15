import React from 'react'
import {Field} from "formik"
import FormGroup from "@react/components/form/FormGroup"

export default function({ children, name, options = {}, required = false, ...props })
{
    const { useFormik = false, ...propsFiltered} = props

    const label = (
        <label
            htmlFor={name}
            className={required ? "required" : ""}
        >
            {children}
        </label>
    )

    const elementOptions = (
        Object.entries(options).map(([key, option]) => {
            return (
                <option
                    value={key}
                    key={option}
                >
                    {option}
                </option>
            )
        })
    )

    const input = useFormik ? (
        <Field
            component="select"
            id={name}
            name={name}
            {...propsFiltered}
        >
            { elementOptions }
        </Field>
    ) : (
        <select
            id={name}
            name={name}
            {...propsFiltered}
        >
            { elementOptions }
        </select>
    )

    return (
        <FormGroup>
            {label}
            {input}
        </FormGroup>
    )
}
