import React from 'react';
import ReactDOM from 'react-dom';
import { RecoilRoot, useRecoilValue } from 'recoil';
import RecoilDempState from './../debug/RecoilDumpState';
import { fieldControlState } from './../controls/atom/fieldControlState';
import { fieldValuesState } from './recoil/atom/editorStates';

import Relation from './relation';
import Controls from './../controls';

export default function Main({config, content}) {
    const control = useRecoilValue(fieldControlState);
    const {last, current} = useRecoilValue(fieldValuesState);

    const allowSave = (f, s) => {
        if (f.length !== s.length) return true;

        let missing = false;

        s.forEach(i => {
            if(! f.includes(i)) missing = true;
        });

        return missing;
    }

    return (
        <div>
            <div className="d-flex justify-content-between">
                <div id={config.contentId} dangerouslySetInnerHTML={{__html: content}}></div>
                <div>
                    <Controls showSave={allowSave(last, current)} />
                </div>
            </div>

            { control.editing && <Relation {...config} /> }

            <RecoilDempState atom={fieldValuesState} />
        </div>
    );
}

document.querySelectorAll('._inplace-field-control').forEach(function(node) {
    const payload = JSON.parse(node.dataset.inplaceFieldConf);

    ReactDOM.render(
        <RecoilRoot>
            <Main config={payload} content={node.innerHTML} />
        </RecoilRoot>, node
    );
});