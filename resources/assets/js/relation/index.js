import React, { useState, useEffect, useCallback } from 'react';
import ReactDOM from 'react-dom';
import Relation from './relation';
import Controls from './../controls';

export default function Main({payload}) {
    const [editing, setEditing] = useState(false);

    const handleToggleEdit = (status) => setEditing(status);

    return (
        <div>
            <Controls editing={editing} onEditToggle={handleToggleEdit} />

            { editing && <Relation {...payload} /> }
        </div>
    );
}

document.querySelectorAll('._inplace-field-control').forEach(function(node) {
    const payload = JSON.parse(node.dataset.inplaceFieldConf);

    ReactDOM.render(<Main payload={payload} />, node);
});