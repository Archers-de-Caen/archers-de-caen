import React, { Component, PropsWithChildren } from 'react';

export default class extends Component<PropsWithChildren> {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <div className="form-groups">
                { this.props.children }
            </div>
        )
    }
}
