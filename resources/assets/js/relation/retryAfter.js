import React, { useEffect, useState, useRef } from 'react';
import { useRecoilState } from 'recoil';
import { fieldRateLimitedState } from './recoil/atom/editorStates';
import { fieldControlState, fieldControlAppearState } from '../controls/atom/fieldControlState';
import formatDistanceToNow from 'date-fns/formatDistanceToNow'
import formatDuration from 'date-fns/formatDuration'
import intervalToDuration from 'date-fns/intervalToDuration'
import fromUnixTime from 'date-fns/fromUnixTime'
import differenceInSeconds from 'date-fns/differenceInSeconds'
import storage from '../utils/storage';
import { _getFieldKey, _hasProperty } from '../utils/misc';

export default function RetryAfter({fieldSignature}) {
    const [blockedFor, setBlockedFor] = useRecoilState(fieldRateLimitedState);
    const [showControls, setShowControls] = useRecoilState(fieldControlAppearState);
    const [control, setControl] = useRecoilState(fieldControlState);

    const [waitTill, setWaitTill] = useState(null);

    const firstMounded = useRef(true);

    const _waitingOver = (unix) => differenceInSeconds(fromUnixTime(unix), new Date()) <= 0;

    const _formatWaitTime = (unix) => {
        if(_waitingOver(unix)) return null;

        const duration = intervalToDuration({
            start: new Date(),
            end: fromUnixTime(unix)
        });

        const formattedDuration = formatDuration(duration, { format: ['hours', 'minutes', 'seconds'], zero: true, delimiter: ' ' });
    
        const regexpCaptureTimeDistance =  /((?<hour>\d+)\shour(s)?\s?)?((?<minute>\d+)\sminute(s)?\s?)?(?<second>\d+)\ssecond(s)?/ig;
        const match = regexpCaptureTimeDistance.exec(formattedDuration);
        if(match === null) return null;

        const {hour, minute, second} = match.groups;

        const hh = typeof hour !== 'undefined' && Number(hour) > 0 ? `${hour}:` : '';
        const mm = typeof minute !== 'undefined' && Number(minute) > 0 ? `${minute.toString().padStart(2, '0')}:` : '';

        const human = formatDistanceToNow(fromUnixTime(unix), { addSuffix: false });

        return `${human} ( ${hh}${mm}${second.toString().padStart(2, '0')} )`;
    }

    /**if the field is still blocked by any prevuois response */
    useEffect(() => {
        if (firstMounded.current) {
            const meta = storage.get(_getFieldKey(fieldSignature));

            if(! _hasProperty(meta, 'X-RateLimit-Reset')) {
                firstMounded.current = false;
                return;
            }

            const secondsRemaining = differenceInSeconds(fromUnixTime(meta['X-RateLimit-Reset']), new Date());
                    
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

            if(_waitingOver(blockedFor.resetUnix)) setBlockedFor({second: 0, resetUnix: null});

            let id = setInterval(() => setWaitTill(_formatWaitTime(blockedFor.resetUnix)), 1000);
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