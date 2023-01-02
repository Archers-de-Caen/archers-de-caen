import React from 'react';
import ArcherRegistration, {
    ArcherRegistrationDef
} from "@react/controllers/competition/registration/ArcherRegistration";
import Toggleables from "@react/components/toggleable/Toggleables";
import {Form, Field, Formik, FormikValues, FieldArray} from 'formik';
import FormGroups from "@react/components/form/FormGroups";
import FormGroup from "@react/components/form/FormGroup";

export interface Registration {
    registrations: Array<ArcherRegistrationDef>,
    additionalInformation: string
}

export default function () {
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
                wheelchair: false,
                firstYear: false,
            }
        ],
        additionalInformation: ''
    }

    function onSubmit(values) {
        alert(JSON.stringify(values, null, 2));
    }

    return (
        <Formik onSubmit={ onSubmit } initialValues={ initialValues }>
            {({ values }: FormikValues) => (
                <Form>
                    <Toggleables>
                        <FieldArray name="registrations">
                            {({ remove, push }) => (
                                <div>
                                    {values.registrations.length > 0 &&
                                        values.registrations.map((registration: ArcherRegistrationDef, index: number) => (
                                            <ArcherRegistration
                                                key={index}
                                                activeByDefault
                                                count={index}
                                                selfRemove={remove}
                                            />
                                    ))}

                                    <div className="flex jc-end">
                                        <button
                                            type="button"
                                            onClick={() => push(initialValues.registrations[0])}
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
                </Form>
            )}
        </Formik>
    )
}
