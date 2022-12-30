import React, { Component } from 'react'
import FormGroups from "@react/components/form/FormGroups"
import FormGroup from "@react/components/form/FormGroup"
import Toggleable, {ToggleableProps, ToggleableState} from "@react/components/toggleable/Toggleable";
import ToggleableSummary from "@react/components/toggleable/ToggleableSummary";
import ToggleableContent from "@react/components/toggleable/ToggleableContent";
import {Field} from "formik";

interface ArcherRegistrationProps extends ToggleableProps {
    count: number
}

export default class extends Component<ArcherRegistrationProps, ToggleableState> {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <Toggleable activeByDefault={this.props.activeByDefault}>
                <ToggleableSummary
                    title="Nouvelle inscription"
                />
                <ToggleableContent>
                    <FormGroups>
                        <FormGroup>
                            <label
                                htmlFor={`registrations${this.props.count}licenseNumber`}
                                className="required"
                            >
                                Numéro de licence
                            </label>
                            <Field
                                type="text"
                                id={`registrations${this.props.count}licenseNumber`}
                                name={`registrations.${this.props.count}.licenseNumber`}
                                pattern="[0-9]{6}[A-Za-z]"
                                placeholder="123456A"
                            />
                        </FormGroup>
                    </FormGroups>

                    <FormGroups>
                        <div className="mt-2">
                            <div className="flex jc-space-between">
                                <FormGroup>
                                    <label
                                        htmlFor={`registrations${this.props.count}firstName`}
                                        className="required"
                                    >
                                        Prénom
                                    </label>
                                    <Field
                                        type="text"
                                        id={`registrations${this.props.count}firstName`}
                                        name={`registrations.${this.props.count}.firstName`}
                                    />
                                </FormGroup>

                                <FormGroup>
                                    <label
                                        htmlFor={`registrations${this.props.count}lastName`}
                                        className="required"
                                    >
                                        Nom
                                    </label>
                                    <Field
                                        type="text"
                                        id={`registrations${this.props.count}lastName`}
                                        name={`registrations.${this.props.count}.lastName`}
                                    />
                                </FormGroup>
                            </div>
                        </div>
                    </FormGroups>

                    <FormGroups>
                        <div className="mt-2">
                            <div className="flex jc-space-between">
                                <FormGroup>
                                    <label
                                        htmlFor={`registrations${this.props.count}email`}
                                        className="required"
                                    >
                                        Email
                                    </label>
                                    <Field
                                        type="text"
                                        id={`registrations${this.props.count}email`}
                                        name={`registrations.${this.props.count}.email`}
                                    />
                                </FormGroup>

                                <FormGroup>
                                    <label
                                        htmlFor={`registrations${this.props.count}phone`}
                                        className="required"
                                    >
                                        Téléphone
                                    </label>
                                    <input
                                        type="text"
                                        id={`registrations${this.props.count}phone`}
                                        name={`registrations.${this.props.count}.phone`}
                                    />
                                </FormGroup>
                            </div>
                        </div>
                    </FormGroups>

                    <FormGroups>
                        <FormGroup>
                            <label
                                htmlFor={`registrations${this.props.count}category`}
                                className="required"
                            >
                                Catégorie
                            </label>
                            <Field
                                component="select"
                                id={`registrations${this.props.count}category`}
                                name={`registrations.${this.props.count}.category`}
                            >
                                <option>Test</option>
                            </Field>
                        </FormGroup>
                    </FormGroups>

                    <FormGroups>
                        <FormGroup>
                            <label
                                htmlFor={`registrations${this.props.count}club`}
                                className="required"
                            >
                                Club
                            </label>
                            <Field
                                type="text"
                                id={`registrations${this.props.count}club`}
                                name={`registrations.${this.props.count}.club`}
                            />
                        </FormGroup>
                    </FormGroups>

                    <FormGroups>
                        <FormGroup check>
                            <label
                                htmlFor={`registrations${this.props.count}wheelchair`}
                                className="required"
                            >
                                Tir en fauteuil roulant
                            </label>
                            <Field
                                type="checkbox"
                                id={`registrations${this.props.count}wheelchair`}
                                name={`registrations.${this.props.count}.wheelchair`}
                            />
                        </FormGroup>
                    </FormGroups>

                    <FormGroups>
                        <FormGroup check>
                            <label
                                htmlFor={`registrations${this.props.count}firstYear`}
                                className="required"
                            >
                                1er année de licence et souhaite effectuer le tir en débutant
                            </label>
                            <Field
                                type="checkbox"
                                id={`registrations${this.props.count}firstYear`}
                                name={`registrations.${this.props.count}.firstYear`}
                            />
                        </FormGroup>
                    </FormGroups>
                </ToggleableContent>
            </Toggleable>
        )
    }
}
