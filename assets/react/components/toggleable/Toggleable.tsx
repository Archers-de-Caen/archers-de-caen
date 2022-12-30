import React, {Component, PropsWithChildren} from 'react'
import ToggleableSummary from "@react/components/toggleable/ToggleableSummary";
import ToggleableContent from "@react/components/toggleable/ToggleableContent";

export interface ToggleableProps extends PropsWithChildren {
    children?: [
        React.ReactElement<ToggleableSummary>,
        React.ReactElement<ToggleableContent>,
    ],
    activeByDefault?: boolean
}

export interface ToggleableState {
    active: boolean
}

export default class extends Component<ToggleableProps, ToggleableState> {
    constructor(props) {
        super(props)

        this.state = {
            active: this.props.activeByDefault ? this.props.activeByDefault : false,
        }
    }

    render() {
        return (
            <div className={"toggleable" + (this.state.active ? ' --active' : '')}>
                <div
                    className="toggleable-summary"
                    onClick={() => this.setState((prevState) => ({ active: !prevState.active }))}
                >
                    { this.props.children[0] }
                </div>

                <div className="toggleable-content">
                    { this.props.children[1] }
                </div>
            </div>
        )
    }
}
