import React, { useEffect, useState, useRef } from 'react';
import { useRecoilState } from 'recoil';
import { fieldRateLimitedState } from './recoil/atom/editorStates';
import { fieldControlState, fieldControlAppearState } from '../controls/atom/fieldControlState';
import moment from 'moment';
import storage from '../utils/storage';
import { getFieldKey, hasProperty } from '../utils/misc';

export default function RetryAfter({fieldSignature}) {
    const [blockedFor, setBlockedFor] = useRecoilState(fieldRateLimitedState);
    const [showControls, setShowControls] = useRecoilState(fieldControlAppearState);
    const [control, setControl] = useRecoilState(fieldControlState);

    const [waitTill, setWaitTill] = useState(null);

    const firstMounded = useRef(true);

    const _formatDistance = (tillUnix) => {
        const till = moment.unix(tillUnix);

        if(till.diff(moment(), 'seconds') < 0) return null;

        const duration = moment.duration(till.diff(moment()));

        const hr = duration.get('hours');
        const min = duration.get('minutes');
        const sec = duration.get('seconds');

        const clock = `${hr > 0 ? hr +':' : ''}${min}:${sec.toString().padStart(2, '0')}`;

        return duration.humanize() +' ( ' + clock + ' )';
    }

    useEffect(() => {
        if (firstMounded.current) {
            const meta = storage.get(getFieldKey(fieldSignature));

            if(! hasProperty(meta, 'X-RateLimit-Reset')) {
                firstMounded.current = false;
                return;
            }

            const secondsRemaining = moment.unix(meta['X-RateLimit-Reset']).diff(moment(), 'seconds');
                    
            if(secondsRemaining > 0) {
                setBlockedFor({ second: secondsRemaining, resetUnix: meta['X-RateLimit-Reset'] });
            }

            firstMounded.current = false;
        }
    }, [])

    useEffect(() => {
        if(blockedFor.second !== 0) {
            if(control.editing) setControl( {save: false, editing: false} );

            if(showControls) setShowControls(false);

            if(moment.unix(blockedFor.resetUnix).diff(moment(), 'seconds') <= 0) setBlockedFor({second: 0, resetUnix: null});

            let id = setInterval(() => setWaitTill(_formatDistance(blockedFor.resetUnix)), 1000);
            return () => clearInterval(id);
        }

        if(waitTill !== null) setWaitTill(null);
        if(! showControls) setShowControls(true);
    }, [blockedFor, waitTill, showControls, control]);

    if(waitTill === null) return null;

    return (
        <span>wait {waitTill}</span>
    );
}