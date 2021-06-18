import React, { useState, useEffect, useCallback } from 'react';
import ReactDOM from 'react-dom';
import Relation from './relation';
import Controls from './../controls';

export default function Main({payload}) {
    const [editing, setEditing] = useState(false);
    const [save, setSave] = useState(false);

    const handleToggleEdit = useCallback((status) => setEditing(status), []);
    
    const handleSave = useCallback(() => setSave(true), []);
    const handleSaveFinished = useCallback(() => setSave(false), []);

    return (
        <div>
            <Controls editing={editing} onEditToggle={handleToggleEdit} onSave={handleSave} />

            { editing && <Relation {...payload} save={save} onSaveFinished={handleSaveFinished} /> }
        </div>
    );
}

document.querySelectorAll('._inplace-field-control').forEach(function(node) {
    const payload = JSON.parse(node.dataset.inplaceFieldConf);

    ReactDOM.render(<Main payload={payload} />, node);
});