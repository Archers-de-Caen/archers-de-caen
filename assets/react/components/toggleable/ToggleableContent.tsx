import React, {Component, PropsWithChildren} from 'react'

export default class extends Component<PropsWithChildren> {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <>
                { this.props.children }
            </>
        )
    }
}
