import React from 'react'

export default function ({ title, children = null }) {
    return (
        <>
            <h4>{ title }</h4>
            { children ? <div>{ children }</div> : ''}
        </>
    )
}
