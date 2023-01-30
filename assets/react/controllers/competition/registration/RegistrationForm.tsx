import React from 'react'
import ArcherRegistration from "@react/controllers/competition/registration/ArcherRegistration"
import {ArcherRegistration as ArcherRegistrationDef} from "@react/controllers/competition/registration/types/ArcherRegistration"
import Toggleables from "@react/components/toggleable/Toggleables"
import {Form, Formik, FormikValues, FieldArray} from 'formik'
import FormGroups from "@react/components/form/FormGroups"
import FormGroup from "@react/components/form/FormGroup"
import {Departure} from "@react/controllers/competition/registration/types/Departure"
import Field from "@react/components/form/Field"

interface RegistrationFormProps {
    departures: Array<Departure>
}

export default function ({ departures }: RegistrationFormProps) {
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
                departures: []
            }
        ],
        additionalInformation: ''
    }

    function onSubmit(values) {
        const registrations = []

        for (const value of values) {
            for (const departure of value.departures) {
                    registrations.push({
                        licenseNumber: value.licenseNumber,
                        firstName: value.firstName,
                        lastName: value.lastName,
                        email: value.email,
                        phone: value.phone,
                        category: value.category,
                        club: value.club,
                        wheelchair: value.wheelchair,
                        firstYear: value.firstYear,
                        departure: departure.departure,
                        target: departure.target,
                        weapon: departure.weapon,
                })
            }
        }

        console.log(registrations)

        alert(JSON.stringify(values, null, 2));
    }

    return (
        <Formik onSubmit={ onSubmit } initialValues={ initialValues }>
            {({ values, errors }: FormikValues) => {
                return (
                    <Form>
                        <Toggleables>
                            <FieldArray name="registrations">
                                {({remove, push}) => (
                                    <div>
                                        {values.registrations.length > 0 &&
                                            values.registrations.map((registration: ArcherRegistrationDef, index: number) => (
                                                <ArcherRegistration
                                                    key={index}
                                                    activeByDefault
                                                    registrationNumber={index}
                                                    selfRemove={remove}
                                                    departures={departures}
                                                    errors={errors.registrations ? errors.registrations[index] : null}
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
                                    useFormik
                                    component="textarea"
                                    id={`additionalInformation`}
                                    name={`additionalInformation`}
                                    placeholder="Avez-vous des précisions à faire ?"
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
                                        Valider
                                    </button>
                                </div>
                            </FormGroup>
                        </FormGroups>
                    </Form>
                )
            }}
        </Formik>
    )
}
