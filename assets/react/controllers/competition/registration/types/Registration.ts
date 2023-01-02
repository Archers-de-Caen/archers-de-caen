import {
    ArcherRegistration as ArcherRegistrationDef
} from "@react/controllers/competition/registration/types/ArcherRegistration";

export interface Registration {
    registrations: Array<ArcherRegistrationDef>,
    additionalInformation: string
}
