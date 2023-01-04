import React, {ChangeEvent, useState} from 'react'
import RadioField from "@react/components/form/RadioField"
import {Departure} from "@react/controllers/competition/registration/types/Departure"
import {Target} from "@react/controllers/competition/registration/types/Target"

interface WeaponChoiceProps {
    registrationNumber: number,
    departure: Departure,
    target: Target,
    weapon: string,
    onChange: any
}

export default function ({ registrationNumber, departure, target, weapon, onChange }: WeaponChoiceProps)
{
    return (
        <RadioField
            asButton
            name={`registrations.${registrationNumber}.departures.${departure.id}.targets.${target.id}.weapons`}
            id={`registrations.${registrationNumber}.departures.${departure.id}.targets.${target.id}.weapons.${weapon}`}
            value={weapon}
            onChange={onChange}
        >
            {weapon}
        </RadioField>
    )
}
