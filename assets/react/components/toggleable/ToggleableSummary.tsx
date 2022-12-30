import React, {Component, PropsWithChildren} from 'react'

interface ToggleableSummaryProps extends PropsWithChildren {
    title: string,
    children?: React.ReactElement
}

export default class extends Component<ToggleableSummaryProps> {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <>
                <h4>{ this.props.title }</h4>
                { this.props.children ? <div>{ this.props.children }</div> : ''}
            </>
        )
    }
}
