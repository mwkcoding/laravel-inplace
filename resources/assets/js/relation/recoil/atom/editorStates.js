import { atom } from 'recoil';

const fieldValuesState = atom({
    key: 'fieldValuesState',
    default: {
        last: [],
        current: []
    },
});

export { fieldValuesState };