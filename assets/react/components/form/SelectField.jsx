import React from 'react'
import {Field} from "formik"
import FormGroup from "@react/components/form/FormGroup"

export default function({ children, name, options = {}, required = false, ...props })
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
                component="select"
                id={name}
                name={name}
                {...props}
            >
                { Object.entries(options).map(([key, option]) => {
                    return (
                        <option
                            value={key}
                            key={option}
                        >
                            {option}
                        </option>
                    )
                }) }
            </Field>
        </FormGroup>
    )
}
