import React, {Component} from 'react';
import Toggleable from "@react/components/toggleable/Toggleable";

interface ToggleablesProps {
    children: React.ReactElement<Toggleable>[] | React.ReactElement<Toggleable>
}

export default class extends Component<ToggleablesProps> {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <div className="toggleables">
                { this.props.children }
            </div>
        );
    }
}
