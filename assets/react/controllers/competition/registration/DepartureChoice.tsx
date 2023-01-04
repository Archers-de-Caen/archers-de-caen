import React, {useEffect, useState} from 'react'
import CheckboxField from "@react/components/form/CheckboxField"
import {Target} from "@react/controllers/competition/registration/types/Target"
import {Departure} from "@react/controllers/competition/registration/types/Departure"
import FormGroups from "@react/components/form/FormGroups"
import {FormikContextType, useFormikContext} from "formik";
import {Registration} from "@react/controllers/competition/registration/types/Registration";
import RadioField from "@react/components/form/RadioField"

interface DepartureChoiceProps {
    registrationNumber: number,
    departure: Departure
}

export interface DepartureChoiceStates {
    departure: string,
    target: string,
    weapon: string,
}

const departureInitialValue: DepartureChoiceStates = {
    departure: '',
    target: '',
    weapon: '',
}

export default function ({ registrationNumber, departure }: DepartureChoiceProps)
{
    const [ departureChecked, setDepartureChecked ] = useState(departureInitialValue)
    const { values, setFieldValue }: FormikContextType<Registration> = useFormikContext()

    const handleDepartureChange = (e) => {
        if (!departureChecked || !departureChecked.departure) {
            setDepartureChecked({
                departure: e.target.value,
                target: '',
                weapon: '',
            })
        } else {
            setDepartureChecked(departureInitialValue)
        }
    }

    const handleTargetChange = (e) => {
        setDepartureChecked({
            departure: departureChecked.departure,
            target: e.target.value,
            weapon: '',
        })
    }

    const handleWeaponChange = (e) => {
        setDepartureChecked({
            departure: departureChecked.departure,
            target: departureChecked.target,
            weapon: e.target.value,
        })
    }

    useEffect(() => {
        const departures = values.registrations[registrationNumber].departures
        let departureFind = null

        for (const key in departures) {
            if (departureChecked.departure === departures[key].departure) {
                departures[key] = departureChecked

                departureFind = true
            }
        }

        if (!departureFind) {
            departures.push(departureChecked)
        }

        setFieldValue(`registrations.${registrationNumber}.departures`, departures)
    }, [departureChecked])

    return (
        <>
            <CheckboxField
                asButton
                name={`registrations.${registrationNumber}.departures`}
                id={`registrations.${registrationNumber}.departures.${departure.id}`}
                value={departure.id}
                onChange={handleDepartureChange}
                checked={departureChecked && departureChecked.departure === departure.id}
            >
                Départ du {(new Date(departure.date)).toLocaleString()} (
                {departure.numberOfRegistered} inscrits / {departure.maxRegistration})
            </CheckboxField>

            { departureChecked && departureChecked.departure === departure.id && (
                <FormGroups
                    className="w-90 flex direction-column item-center"
                    key={registrationNumber + '_' + departure.id + '_targets'}
                >
                    { departure.targets.map((target: Target) => (
                        <div
                            className="w-100 flex direction-column item-center"
                            key={registrationNumber + '_' + departure.id + '_' + target.id}
                        >
                            <RadioField
                                asButton
                                name={`registrations.${registrationNumber}.departures.${departure.id}.targets`}
                                id={`registrations.${registrationNumber}.departures.${departure.id}.targets.${target.id}`}
                                value={target.id}
                                onChange={handleTargetChange}
                                checked={departureChecked.target === target.id}
                            >
                                {target.type} à {target.distance}m
                            </RadioField>

                            { departureChecked && departureChecked.target === target.id && (
                                <div
                                    className="w-90 flex --gap-3 --wrap"
                                    key={registrationNumber + '_' + departure.id + '_' + target.id + '_weapons'}
                                >
                                    { ['recurve_bow', 'compound_bow', 'bare_bow'].map((weapon: string) => (
                                        <RadioField
                                            key={registrationNumber + '_' + departure.id + '_' + target.id + '_' + weapon}
                                            asButton
                                            name={`registrations.${registrationNumber}.departures.${departure.id}.targets.${target.id}.weapons`}
                                            id={`registrations.${registrationNumber}.departures.${departure.id}.targets.${target.id}.weapons.${weapon}`}
                                            value={weapon}
                                            onChange={handleWeaponChange}
                                            checked={departureChecked.weapon === weapon}
                                        >
                                            {weapon}
                                        </RadioField>
                                    ))}
                                </div>
                            )}
                        </div>
                    )) }
                </FormGroups>
            ) }
        </>
    )
}
