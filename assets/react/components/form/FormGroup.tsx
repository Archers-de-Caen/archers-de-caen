import React, { Component, PropsWithChildren } from 'react';

interface FormGroupProps extends PropsWithChildren {
    check?: boolean
}

export default class extends Component<FormGroupProps> {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <div className={"form-group" + (this.props.check ? ' --check' : '')}>
                { this.props.children }
            </div>
        )
    }
}
