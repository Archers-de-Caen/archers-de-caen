import React, {ChangeEvent, useEffect, useState} from 'react'
import CheckboxField from "@react/components/form/CheckboxField"
import TargetChoice from "@react/controllers/competition/registration/TargetChoice"
import {Target} from "@react/controllers/competition/registration/types/Target"
import {Departure} from "@react/controllers/competition/registration/types/Departure"
import FormGroups from "@react/components/form/FormGroups"
import WeaponChoice from "@react/controllers/competition/registration/WeaponChoice";

interface DepartureChoiceProps {
    registrationNumber: number,
    departure: Departure
}

export default function ({ registrationNumber, departure }: DepartureChoiceProps)
{
    const [departureChecked, setDepartureChecked] = useState('')
    const [targetChecked, setTargetChecked] = useState('')

    return (
        <div>
            <CheckboxField
                asButton
                name={`registrations.${registrationNumber}.departures`}
                id={`registrations.${registrationNumber}.departures.${departure.id}`}
                value={departure.id}
                onChange={(e) => setDepartureChecked((prev: string) => !prev ? e.target.value : null)}
                checked={departureChecked === departure.id}
            >
                Départ du {(new Date(departure.date)).toLocaleString()} (
                {departure.numberOfRegistered} inscrits / {departure.maxRegistration})
            </CheckboxField>

            { departureChecked === departure.id && (
                <FormGroups>
                    { departure.targets.map((target: Target) => (
                        <TargetChoice
                            registrationNumber={registrationNumber}
                            departure={departure}
                            target={target}
                            key={registrationNumber + '_' + departure.id + '_' + target.id}
                            onChange={(e: ChangeEvent<HTMLInputElement>) => setTargetChecked(() => e.target.value )}
                            checked={targetChecked === target.id}
                        />
                    )) }
                </FormGroups>
            ) }
        </div>
    )
}
