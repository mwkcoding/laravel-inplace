import React, { useEffect } from 'react';
import { useRecoilState } from 'recoil';
import { fieldControlState } from './atom/fieldControlState';
import useKeyPress from '../utils/hooks/useKeyPress';

function Controls({showSave}) {
    const [control, setControl] = useRecoilState(fieldControlState);

    const handleCancel = () => setControl( {save: false, editing: false} );
    const handleEdit = () => setControl( {save: false, editing: true} );
    const handleSave = () => setControl( (prevState) => ({...prevState, save: true }) );

    const escaped = useKeyPress(27);

    useEffect(() => {
        if(escaped && control.editing) handleCancel();
    }, [escaped, control.editing]);

    return (
        control.editing ? 
        <>
        {showSave && <button type="button" onClick={handleSave}>save</button> }
        <button type="button" onClick={handleCancel}>calcel</button>
        </>
        :
        <button type="button" onClick={handleEdit}>edit</button>
    )
}

export default React.memo(Controls);