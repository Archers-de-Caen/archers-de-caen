import React from 'react'
import {Field} from "formik"
import FormGroup from "@react/components/form/FormGroup"

export default function({ children, name, required = false, btn = false, ...props })
{
    return (
        <FormGroup check>
            {
                btn ? (
                    <>
                        <Field
                            type="checkbox"
                            id={name}
                            name={name}
                            required={required}
                            {...props}
                        />
                        <label
                            htmlFor={name}
                            className={required ? "required" : ""}
                        >
                            {children}
                        </label>
                    </>
                ) : (
                    <>
                        <label
                            htmlFor={name}
                            className={required ? "required" : ""}
                        >
                            {children}
                        </label>
                        <Field
                            type="checkbox"
                            id={name}
                            name={name}
                            required={required}
                            {...props}
                        />
                    </>
                )
            }

        </FormGroup>
    )
}
