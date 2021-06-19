import React from 'react';
import { useRecoilState } from 'recoil';
import { fieldControlState } from './atom/fieldControlState';

function Controls() {
    const [control, setControl] = useRecoilState(fieldControlState);

    return (
        control.editing ? 
        <>
        <button type="button" onClick={ () => setControl((prevState) => ({...prevState, save: true }) ) }>save</button>
        <button type="button" onClick={ () => setControl( {save: false, editing: false} ) }>calcel</button>
        </>
        :
        <button type="button" onClick={ () => setControl((prevState) => ({...prevState, editing: true }) ) }>edit</button>
    )
}

export default React.memo(Controls);