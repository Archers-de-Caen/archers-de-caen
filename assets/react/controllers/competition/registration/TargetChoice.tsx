import React, {ChangeEvent, useEffect, useState} from 'react'
import RadioField from "@react/components/form/RadioField"
import WeaponChoice from "@react/controllers/competition/registration/WeaponChoice"
import {Departure} from "@react/controllers/competition/registration/types/Departure"
import {Target} from "@react/controllers/competition/registration/types/Target"

interface TargetChoiceProps {
    registrationNumber: number,
    departure: Departure,
    target: Target,
    onChange: any,
    checked: any,
}

export default function ({ registrationNumber, departure, target, onChange, checked }: TargetChoiceProps)
{
    const [, setWeaponChecked] = useState('')

    return (
        <>
            <RadioField
                asButton
                name={`registrations.${registrationNumber}.departures.${departure.id}.targets`}
                id={`registrations.${registrationNumber}.departures.${departure.id}.targets.${target.id}`}
                value={target.id}
                onChange={onChange}
            >
                {target.type} à {target.distance}m
            </RadioField>

            { checked && (
                <div
                    className="flex --gap-3"
                >
                    { ['recurve_bow', 'compound_bow', 'bare_bow'].map((weapon: string) => (
                        <WeaponChoice
                            registrationNumber={registrationNumber}
                            departure={departure}
                            target={target}
                            weapon={weapon}
                            key={registrationNumber + '_' + departure.id + '_' + target.id + '_' + weapon}
                            onChange={(e: ChangeEvent<HTMLInputElement>) => setWeaponChecked(() => e.target.value )}
                        />
                    ))}
                </div>
            )}
        </>

    )
}
