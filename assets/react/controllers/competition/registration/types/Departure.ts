import {Target} from "@react/controllers/competition/registration/types/Target";

export interface Departure {
    id: string,
    date: Date,
    maxRegistration: number,
    targets: Array<Target>,
    numberOfRegistered: number
}
