import React, {useState} from 'react'
import FormGroups from "@react/components/form/FormGroups"
import FormGroup from "@react/components/form/FormGroup"
import Toggleable from "@react/components/toggleable/Toggleable"
import ToggleableSummary from "@react/components/toggleable/ToggleableSummary"
import ToggleableContent from "@react/components/toggleable/ToggleableContent"
import Field from "@react/components/form/Field"
import CheckboxField from "@react/components/form/CheckboxField"
import SelectField from "@react/components/form/SelectField"
import {Registration} from "@react/controllers/competition/registration/types/Registration"
import {FormikContextType, useFormikContext} from "formik"
import Swal from 'sweetalert2'
import {Departure} from "@react/controllers/competition/registration/types/Departure"
import DepartureChoice from "@react/controllers/competition/registration/DepartureChoice";

interface ArcherRegistrationProps {
    registrationNumber: number,
    selfRemove: Function,
    activeByDefault: boolean,
    departures: Array<Departure>
}

function getArcherInformation(licenseNumber: string): Promise<any>
{
    return fetch('/api/competition-registers/archers/' + licenseNumber)
        .then((response: Response) => {
            if (200 === response.status) {
                return response.json()
            }
        })
}

export default function ({ registrationNumber, selfRemove, departures = [], activeByDefault = false }: ArcherRegistrationProps)
{
    const [ timeoutId, setTimeoutId ] = useState(null)
    const { values, setFieldValue }: FormikContextType<Registration> = useFormikContext()
    const curentRegistration = values.registrations[registrationNumber]

    const confirmSelfRemove = async () => {
        await Swal.fire({
            title: 'Êtes vous sûr ?',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            showCancelButton: true,
            preConfirm: () => selfRemove(registrationNumber),
        })
    }

    const handleChangeLicenseNumber = (event: React.ChangeEvent<HTMLInputElement>) => {
        const doneTypingInterval = 500

        const licenseNumber = event.target.value.replaceAll(' ', '')

        setFieldValue(`registrations.${registrationNumber}.licenseNumber`, licenseNumber)

        if (timeoutId) {
            clearTimeout(timeoutId)
        }

        setTimeoutId(setTimeout(() => setArcherInformation(licenseNumber), doneTypingInterval))
    }

    const setArcherInformation = (licenseNumber: string) => {
        getArcherInformation(licenseNumber)
            .then((body) => {
                const prefix = `registrations.${registrationNumber}.`

                const fields = [
                    'firstName',
                    'lastName',
                    'email',
                    'phone',
                    'category',
                    'club',
                    'wheelchair',
                ]

                if (body) {
                    for (const field of fields) {
                        setFieldValue(prefix + field, body[field])
                    }
                } else {
                    for (const field of fields) {
                        setFieldValue(prefix + field, '')
                    }
                }
            })
    }

    return (
        <Toggleable activeByDefault={activeByDefault}>
            <ToggleableSummary
                title={curentRegistration.firstName ? `${curentRegistration.firstName} ${curentRegistration.lastName ?? ''}` : "Nouvelle inscription"}
            />
            <ToggleableContent>
                <FormGroups>
                    <Field
                        useFormik
                        name={`registrations.${registrationNumber}.licenseNumber`}
                        pattern="[0-9]{6}[A-Za-z]"
                        placeholder="123456A"
                        onChange={ handleChangeLicenseNumber }
                    >
                        Numéro de licence
                    </Field>
                </FormGroups>

                <FormGroups>
                    <div className="mt-2">
                        <div className="flex jc-space-between --gap-3">
                            <Field
                                useFormik
                                name={`registrations.${registrationNumber}.firstName`}
                            >
                                Prénom
                            </Field>

                            <Field
                                useFormik
                                name={`registrations.${registrationNumber}.lastName`}
                            >
                                Nom
                            </Field>
                        </div>
                    </div>
                </FormGroups>

                <FormGroups>
                    <div className="mt-2">
                        <div className="flex jc-space-between --gap-3">
                            <Field
                                useFormik
                                name={`registrations.${registrationNumber}.email`}
                            >
                                Email
                            </Field>

                            <Field
                                useFormik
                                name={`registrations.${registrationNumber}.phone`}
                            >
                                Téléphone
                            </Field>
                        </div>
                    </div>
                </FormGroups>

                <FormGroups>
                    <SelectField
                        useFormik
                        name={`registrations.${registrationNumber}.category`}
                        options={{
                            peewee: 'Poussin',
                            benjamin: 'Benjamin',
                            cub: 'Minime',
                            cadet: 'Cadet',
                            junior: 'Junior',
                            senior_one: 'Senior 1',
                            senior_two: 'Senior 2',
                            senior_three: 'Senior 3',
                        }}
                    >
                        Catégorie
                    </SelectField>
                </FormGroups>

                <FormGroups>
                    <Field
                        useFormik
                        name={`registrations.${registrationNumber}.club`}
                    >
                        Club
                    </Field>
                </FormGroups>

                <FormGroups>
                    <CheckboxField
                        useFormik
                        name={`registrations.${registrationNumber}.wheelchair`}
                    >
                        Tir en fauteuil roulant
                    </CheckboxField>
                </FormGroups>

                <FormGroups>
                    <CheckboxField
                        useFormik
                        name={`registrations.${registrationNumber}.firstYear`}
                    >
                        1er année de licence et souhaite effectuer le tir en débutant
                    </CheckboxField>
                </FormGroups>

                <FormGroups>
                    <h3>Sélectionner le ou les départs que vous voulez faire</h3>

                    <FormGroup check asButton>
                        <FormGroups className="w-100">
                            { departures.map((departure: Departure) => (
                                <DepartureChoice
                                    registrationNumber={registrationNumber}
                                    departure={departure}
                                    key={registrationNumber + '_' + departure.id}
                                />
                            )) }
                        </FormGroups>
                    </FormGroup>
                </FormGroups>

                <div className="w-100 flex jc-end">
                    <button
                        type="button"
                        className="btn -danger"
                        onClick={ confirmSelfRemove }
                    >
                        Supprimer
                    </button>
                </div>
            </ToggleableContent>
        </Toggleable>
    )
}
