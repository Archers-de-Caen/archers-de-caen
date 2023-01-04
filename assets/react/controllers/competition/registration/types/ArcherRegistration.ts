export interface ArcherRegistration {
    licenseNumber: string,
    firstName: string,
    lastName: string,
    email: string,
    phone: string,
    category: string,
    club: string,
    wheelchair: boolean,
    firstYear: boolean,
    departures: Array<{
        departure: string,
        target: string,
        weapon: string,
    }>
}
