import React from 'react'
import RadioField from "@react/components/form/RadioField"
import {Departure} from "@react/controllers/competition/registration/types/Departure"
import {Target} from "@react/controllers/competition/registration/types/Target"

interface TargetChoiceProps {
    registrationNumber: number,
    departure: Departure,
    target: Target,
    checked: boolean,
    onChange: Function,
}

export default function ({ registrationNumber, departure, target, checked, onChange }: TargetChoiceProps)
{
    return (
        <>
            <RadioField
                asButton
                name={`registrations.${registrationNumber}.departures.${departure.id}.targets`}
                id={`registrations.${registrationNumber}.departures.${departure.id}.targets.${target.id}`}
                value={target.id}
                onChange={onChange}
                checked={checked}
            >
                {target.type} à {target.distance}m
            </RadioField>
        </>
    )
}
