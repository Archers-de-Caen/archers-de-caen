import React from 'react';
import ArcherRegistration from "@react/controllers/competition/registration/ArcherRegistration";
import Toggleables from "@react/components/toggleable/Toggleables";
import {Form as FormikForm, Field, Formik, FormikValues, useFormik, FieldArray} from 'formik';

interface FormState {
    registrations: Array<object>
}

const Form = () => {
    const initialValues = {
        registrations: [
            {
                licenseNumber: '',
                firstName: '',
                lastName: '',
                email: '',
                phone: '',
                category: '',
                club: '',
                wheelchair: '',
                firstYear: '',
            }
        ],
    }

    const onSubmit = (values) => {
        alert(JSON.stringify(values, null, 2));
    }

    return (
        <Formik onSubmit={onSubmit} initialValues={initialValues}>
            {({ values }: FormikValues) => (
                <FormikForm>
                    <Toggleables>
                        <FieldArray name="registrations">
                            {({ insert, remove, push }) => (
                                <div>
                                    {values.registrations.length > 0 &&
                                        values.registrations.map((registration, index) => (
                                            <ArcherRegistration
                                                activeByDefault
                                                count={index}
                                                key={index}
                                            />
                                    ))}

                                    <button
                                        type="button"
                                        onClick={() => push({  })}
                                    >
                                        Ajouter
                                    </button>
                                </div>
                            )}
                        </FieldArray>
                    </Toggleables>
                    <button type="submit">Invite</button>
                </FormikForm>
            )}
        </Formik>
    )
}

export default Form
