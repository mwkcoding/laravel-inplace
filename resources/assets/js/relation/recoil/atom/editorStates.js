import { atom } from 'recoil';

const fieldValuesState = atom({
    key: 'fieldValuesState',
    default: {
        last: [],
        current: []
    },
});

const fieldRateLimitedState = atom({
    key: 'fieldRateLimitedState',
    default: {
        second: 0,
        resetUnix: null
    },
});

export { fieldValuesState, fieldRateLimitedState };