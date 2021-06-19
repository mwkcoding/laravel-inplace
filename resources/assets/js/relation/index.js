import React from 'react';
import ReactDOM from 'react-dom';
import { RecoilRoot, useRecoilState, useRecoilValue } from 'recoil';
import RecoilDempState from './../debug/RecoilDumpState';
import { fieldControlState } from './../controls/atom/fieldControlState';
import { fieldValuesState } from './recoil/atom/editorStates';

import Relation from './relation';
import Controls from './../controls';

export default function Main({payload}) {
    const control = useRecoilValue(fieldControlState);
    const {last, current} = useRecoilValue(fieldValuesState);

    const allowSave = (f, s) => {
        if (f.length !== s.length) return true;
    
        s.forEach(i => {
            if(! f.includes(i)) return true;
        });

        return false;
    }

    return (
        <div>
            <Controls showSave={allowSave(last, current)} />

            {/* <RecoilDempState atom={fieldValuesState} /> */}

            { control.editing && <Relation {...payload} /> }
        </div>
    );
}

document.querySelectorAll('._inplace-field-control').forEach(function(node) {
    const payload = JSON.parse(node.dataset.inplaceFieldConf);

    ReactDOM.render(
        <RecoilRoot>
            <Main payload={payload} />
        </RecoilRoot>, node
    );
});