import React, {useState} from 'react'
import FormGroups from "@react/components/form/FormGroups"
import FormGroup from "@react/components/form/FormGroup"
import Toggleable from "@react/components/toggleable/Toggleable"
import ToggleableSummary from "@react/components/toggleable/ToggleableSummary"
import ToggleableContent from "@react/components/toggleable/ToggleableContent"
import Field from "@react/components/form/Field"
import CheckboxField from "@react/components/form/CheckboxField"
import SelectField from "@react/components/form/SelectField"
import {FormikContextType, useFormikContext} from "formik"
import Swal from 'sweetalert2'
import {Registration} from "@react/controllers/competition/registration/RegistrationForm";

export interface ArcherRegistrationDef {
    licenseNumber: string,
    firstName: string,
    lastName: string,
    email: string,
    phone: string,
    category: string,
    club: string,
    wheelchair: boolean,
    firstYear: boolean,
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

export default function ({ count, selfRemove, activeByDefault = false })
{
    const [ timeoutId, setTimeoutId ] = useState(null)
    const { values, setFieldValue }: FormikContextType<Registration> = useFormikContext()
    const registration = values.registrations[count]

    const confirmSelfRemove = async () => {
        await Swal.fire({
            title: 'Êtes vous sûr ?',
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            showCancelButton: true,
            preConfirm: () => selfRemove(count),
        })
    }

    const handleChangeLicenseNumber = (event: React.ChangeEvent<HTMLInputElement>) => {
        const doneTypingInterval = 500

        const licenseNumber = event.target.value.replaceAll(' ', '')

        setFieldValue(`registrations.${count}.licenseNumber`, licenseNumber)

        if (timeoutId) {
            clearTimeout(timeoutId)
        }

        setTimeoutId(setTimeout(() => setArcherInformation(licenseNumber), doneTypingInterval))
    }

    const setArcherInformation = (licenseNumber: string) => {
        getArcherInformation(licenseNumber)
            .then((body) => {
                const prefix = `registrations.${count}.`

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
                title={registration.firstName ? `${registration.firstName} ${registration.lastName ?? ''}` : "Nouvelle inscription"}
            />
            <ToggleableContent>
                <FormGroups>
                    <Field
                        name={`registrations.${count}.licenseNumber`}
                        pattern="[0-9]{6}[A-Za-z]"
                        placeholder="123456A"
                        onChange={ handleChangeLicenseNumber }
                    >
                        Numéro de licence
                    </Field>
                </FormGroups>

                <FormGroups>
                    <div className="mt-2">
                        <div className="flex jc-space-between">
                            <Field name={`registrations.${count}.firstName`}>
                                Prénom
                            </Field>
                            <Field name={`registrations.${count}.lastName`}>
                                Nom
                            </Field>
                        </div>
                    </div>
                </FormGroups>

                <FormGroups>
                    <div className="mt-2">
                        <div className="flex jc-space-between">
                            <Field name={`registrations.${count}.email`}>
                                Email
                            </Field>

                            <Field name={`registrations.${count}.phone`}>
                                Téléphone
                            </Field>
                        </div>
                    </div>
                </FormGroups>

                <FormGroups>
                    <FormGroup>
                        <SelectField
                            name={`registrations.${count}.category`}
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
                    </FormGroup>
                </FormGroups>

                <FormGroups>
                    <Field name={`registrations.${count}.club`}>
                        Club
                    </Field>
                </FormGroups>

                <FormGroups>
                    <CheckboxField name={`registrations.${count}.wheelchair`}>
                        Tir en fauteuil roulant
                    </CheckboxField>
                </FormGroups>

                <FormGroups>
                    <CheckboxField name={`registrations.${count}.firstYear`}>
                        1er année de licence et souhaite effectuer le tir en débutant
                    </CheckboxField>
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
