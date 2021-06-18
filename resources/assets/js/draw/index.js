import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import RelationEditor from './../relation';

export default function Draw({payload}) {
    const [editing, setEditing] = useState(false);

    const handleCalcel = () => {
        ReactDOM.unmountComponentAtNode(document.getElementById(payload.fieldId));

        setEditing(false);
    }

    const handleEdit = () => {
        const optionsBank = window._inplace.options.relation.find(opt => opt.id === payload.hash);

        const options = optionsBank ? optionsBank.options : [];

        ReactDOM.render(
            <RelationEditor {...payload} options={options} />,
            document.getElementById(payload.fieldId)
        );

        setEditing(true);
    }

    return (
        editing ? 
        <button type="button" onClick={handleCalcel}>calcel</button> :
        <button type="button" onClick={handleEdit}>edit</button>
    )
}

document.querySelectorAll('._inplace-field-control').forEach(function(node) {
    const payload = JSON.parse(node.dataset.inplaceFieldConf);

    ReactDOM.render(<Draw payload={payload} />, node);
});