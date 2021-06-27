import React from 'react';
import ReactDOM from 'react-dom';
import { RecoilRoot, useRecoilValue } from 'recoil';
import RecoilDempState from './../debug/RecoilDumpState';
import { fieldControlState } from './../controls/atom/fieldControlState';
import { fieldValuesState } from './recoil/atom/editorStates';
import RetryAfter from './retryAfter';
import styled from 'styled-components';

import Relation from './relation';
import Controls from './../controls';

const Content = styled.div`
display: flex;
justify-content: space-between;
`;

export default function Main({config, content}) {
    const control = useRecoilValue(fieldControlState);
    const {last, current} = useRecoilValue(fieldValuesState);

    const _isNotSame = (f, s) => {
        if (f.length !== s.length) return true;

        let missing = false;

        s.forEach(i => {
            if(! f.includes(i)) missing = true;
        });

        return missing;
    }

    const allowSave = _isNotSame(last, current);

    return (
        <div>
            <Content>
                <div id={config.contentId} dangerouslySetInnerHTML={{__html: content}}></div>
                <div>
                    <RetryAfter fieldSignature={config.signature} />

                    <Controls showSave={allowSave} />
                </div>
            </Content>

            { control.editing && <Relation {...config} /> }

            {/* <RecoilDempState atom={fieldValuesState} /> */}
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