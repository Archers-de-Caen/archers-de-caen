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
import DepartureChoice from "@react/controllers/competition/registration/DepartureChoice"

interface ArcherRegistrationProps {
    registrationNumber: number,
    selfRemove: Function,
    activeByDefault: boolean,
    departures: Array<Departure>,
    errors: any,
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

export default function ({ registrationNumber, selfRemove, departures = [], activeByDefault = false, errors }: ArcherRegistrationProps)
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

    let title = "Nouvelle inscription"

    if (curentRegistration.licenseNumber) {
        title = curentRegistration.licenseNumber

        if (curentRegistration.firstName) {
            title += ' | ' + curentRegistration.firstName + ' ' + (curentRegistration.lastName ?? '')
        }
    }

    return (
        <Toggleable activeByDefault={activeByDefault}>
            <ToggleableSummary title={title} />
            <ToggleableContent>
                <FormGroups>
                    <Field
                        useFormik
                        name={`registrations.${registrationNumber}.licenseNumber`}
                        pattern="[0-9]{6}[A-Za-z]"
                        placeholder="123456A"
                        onChange={ handleChangeLicenseNumber }
                        errors={ errors && errors.licenseNumber }
                        validate={(value) => {
                            if (value && !/^\d{6}[A-Z]$/i.test(value)) {
                                return 'Doit être au format: 123456A'
                            }
                        }}
                    >
                        Numéro de licence
                    </Field>
                </FormGroups>

                <FormGroups className="mt-2 flex jc-space-between --gap-3 --wrap">
                    <Field
                        useFormik
                        placeholder="Michel"
                        name={`registrations.${registrationNumber}.firstName`}
                    >
                        Prénom
                    </Field>

                    <Field
                        useFormik
                        placeholder="Dupont"
                        name={`registrations.${registrationNumber}.lastName`}
                        className="w-45"
                    >
                        Nom
                    </Field>
                </FormGroups>

                <FormGroups className="mt-2 flex jc-space-between --gap-3 --wrap">
                    <Field
                        useFormik
                        name={`registrations.${registrationNumber}.email`}
                        placeholder="archer@arc.fr"
                        errors={ errors && errors.email }
                        validate={(value) => {
                            if (value && !/^[A-Z0-9*._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(value)) {
                                return 'Ne correspond pas au format d\'un email'
                            }
                        }}
                    >
                        Email
                    </Field>

                    <Field
                        useFormik
                        name={`registrations.${registrationNumber}.phone`}
                        placeholder="06 06 06 06 06"
                    >
                        Téléphone
                    </Field>
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
                        placeholder="Archers de Caen"
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

                <FormGroups className="mt-3">
                    <h3>Sélectionner le ou les départs que vous voulez faire</h3>

                    <FormGroup>
                        <FormGroups className="w-100 flex direction-column item-center">
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