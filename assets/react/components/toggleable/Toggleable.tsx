import React, {useState} from 'react'

export default function ({ children, activeByDefault = false }) {
    const [ active, setActive ] = useState(activeByDefault)

    return (
        <div className={"toggleable" + (active ? ' --active' : '')}>
            <div
                className="toggleable-summary"
                onClick={() => setActive(!active)}
            >
                { children[0] }
            </div>

            <div className="toggleable-content">
                { children[1] }
            </div>
        </div>
    )
}
