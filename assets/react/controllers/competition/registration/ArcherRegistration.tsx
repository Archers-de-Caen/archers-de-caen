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
import {Departure} from "@react/controllers/competition/registration/types/Departure";
import {Target} from "@react/controllers/competition/registration/types/Target";

interface ArcherRegistrationProps {
    count: number,
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

export default function ({ count, selfRemove, departures = [], activeByDefault = false }: ArcherRegistrationProps)
{
    const [ timeoutId, setTimeoutId ] = useState(null)
    const [ departuresSelected, setDeparturesSelected ] = useState([])
    const { values, setFieldValue }: FormikContextType<Registration> = useFormikContext()
    const curentRegistration = values.registrations[count]

    const confirmSelfRemove = async () => {
        await Swal.fire({
            title: 'Êtes vous sûr ?',
            confirmButtonText: 'Oui, supprimer',
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

    const toggleDeparture = (event: React.ChangeEvent<HTMLInputElement>) => {
        const departureSelected = event.target.value

        if (departuresSelected.includes(departureSelected)) {
            setDeparturesSelected(departuresSelected.filter(prevDeparture => prevDeparture !== departureSelected))
        } else {
            setDeparturesSelected((prevDeparturesSelected: Array<string>) => [...prevDeparturesSelected, departureSelected])
        }
    }

    const toggleTarget = (event: React.ChangeEvent<HTMLInputElement>) => {
        const elementTarget = event.target
        const targetSelected = elementTarget.value
        let targets = [...curentRegistration.targets]

        let departureOfTargetSelect = null
        for (const key in departures) {
            if (departures[key].targets.map((target: Target) => target.id).includes(targetSelected)) {
                departureOfTargetSelect = departures[key]
            }
        }

        if (departureOfTargetSelect) {
            for (const target of departureOfTargetSelect.targets.map((target: Target) => target.id)) {
                if (curentRegistration.targets.includes(target)) {
                    targets = targets.filter(prevTarget => prevTarget !== target)

                    setFieldValue(`registrations.${count}.targets`, targets)
                }
            }
        }

        if (targets.includes(targetSelected)) {
            setFieldValue(`registrations.${count}.targets`, targets.filter(prevTarget => prevTarget !== targetSelected))
        } else {
            setFieldValue(`registrations.${count}.targets`, [...targets, targetSelected])
        }
    }

    return (
        <Toggleable activeByDefault={activeByDefault}>
            <ToggleableSummary
                title={curentRegistration.firstName ? `${curentRegistration.firstName} ${curentRegistration.lastName ?? ''}` : "Nouvelle inscription"}
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
                            <FormGroup>
                                <Field name={`registrations.${count}.firstName`}>
                                    Prénom
                                </Field>
                            </FormGroup>

                            <FormGroup>
                                <Field name={`registrations.${count}.lastName`}>
                                    Nom
                                </Field>
                            </FormGroup>
                        </div>
                    </div>
                </FormGroups>

                <FormGroups>
                    <div className="mt-2">
                        <div className="flex jc-space-between">
                            <FormGroup>
                                <Field name={`registrations.${count}.email`}>
                                    Email
                                </Field>
                            </FormGroup>

                            <FormGroup>
                                <Field name={`registrations.${count}.phone`}>
                                    Téléphone
                                </Field>
                            </FormGroup>
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

                <FormGroups>
                    <h3>Sélectionner le ou les départs que vous voulez faire</h3>

                    <FormGroup check btn>
                        <FormGroups className="w-100">
                            {departures.map((departure: Departure) => (
                                <div key={departure.id}>
                                    <FormGroup>
                                        <CheckboxField
                                            btn
                                            name={`departure.${departure.id}`}
                                            value={departure.id}
                                            onChange={toggleDeparture}
                                            checked={departuresSelected.includes(departure.id)}
                                        >
                                            Départ du {(new Date(departure.date)).toLocaleString()} (
                                            {departure.numberOfRegistered} inscrits / {departure.maxRegistration})
                                        </CheckboxField>
                                    </FormGroup>

                                    { departuresSelected.includes(departure.id) &&
                                        departure.targets.map((target: Target) => (
                                            <div key={target.id}>
                                                <FormGroup>
                                                    <CheckboxField
                                                        btn
                                                        name={`registrations.${count}.targets.${target.id}`}
                                                        value={target.id}
                                                        onChange={toggleTarget}
                                                        checked={curentRegistration.targets.includes(target.id)}
                                                    >
                                                        {target.type} à {target.distance}m
                                                    </CheckboxField>
                                                </FormGroup>

                                                <div className="flex">
                                                    { ['recurve_bow', 'compound_bow', 'bare_bow'].map((weapon: string) => (
                                                        <FormGroup
                                                            key={`${target.id}_weapon_${weapon}`}
                                                        >
                                                            <CheckboxField
                                                                btn
                                                                name={`registrations.${count}.targets.${target.id}.${weapon}`}
                                                                value={weapon}
                                                                // onChange={toggleTarget}
                                                                // checked={curentRegistration.targets.includes(target)}
                                                            >
                                                                {weapon}
                                                            </CheckboxField>
                                                        </FormGroup>
                                                    ))}
                                                </div>
                                            </div>
                                        ))
                                    }
                                </div>
                            ))}
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
