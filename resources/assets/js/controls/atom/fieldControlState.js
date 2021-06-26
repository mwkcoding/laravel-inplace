import { atom } from 'recoil';

const fieldControlState = atom({
    key: 'fieldControlState',
    default: {editing: false, save: false},
});

export { fieldControlState };