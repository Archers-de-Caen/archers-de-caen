import React from 'react';
import ArcherRegistration from "@react/controllers/competition/registration/ArcherRegistration";
import Toggleables from "@react/components/toggleable/Toggleables";
import {Form as FormikForm, Field, Formik, FormikValues, useFormik, FieldArray} from 'formik';
import FormGroups from "@react/components/form/FormGroups";
import FormGroup from "@react/components/form/FormGroup";

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
        additionalInformation: ''
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
                                                remove={remove}
                                            />
                                    ))}

                                    <div className="flex jc-end">
                                        <button
                                            type="button"
                                            onClick={() => push(initialValues.registrations)}
                                            className="btn -primary mt-2"
                                        >
                                            Ajouter un archer
                                        </button>
                                    </div>
                                </div>
                            )}
                        </FieldArray>
                    </Toggleables>

                    <FormGroups>
                        <FormGroup>
                            <label
                                htmlFor={`additionalInformation`}
                                className="required"
                            >
                                <h3>Informations complémentaires</h3>
                            </label>
                            <Field
                                component="textarea"
                                id={`additionalInformation`}
                                name={`additionalInformation`}
                            />
                        </FormGroup>
                    </FormGroups>

                    <FormGroups>
                        <FormGroup>
                            <div className="w-100 flex jc-end">
                                <button
                                    id={`submit`}
                                    name={`submit`}
                                    type="submit"
                                    className="btn -primary"
                                >
                                    Suivant
                                </button>
                            </div>
                        </FormGroup>
                    </FormGroups>
                </FormikForm>
            )}
        </Formik>
    )
}

export default Form
