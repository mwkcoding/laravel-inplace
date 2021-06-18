import React from 'react';

function Controls({editing, onEditToggle, onSave}) {
    return (
        editing ? 
        <>
        <button type="button" onClick={onSave}>save</button>
        <button type="button" onClick={() => onEditToggle(false)}>calcel</button>
        </>
        :
        <button type="button" onClick={() => onEditToggle(true)}>edit</button>
    )
}

export default React.memo(Controls);