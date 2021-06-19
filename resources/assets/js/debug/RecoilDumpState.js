import React from 'react';
import { useRecoilCallback } from 'recoil';

export default function RecoilDempState({atom}) {
    const logState = useRecoilCallback(({snapshot}) => () => {
      console.log("State: ", snapshot.getLoadable(atom).contents);
    });

    return (
        <button onClick={logState}>Log</button>
    )
}