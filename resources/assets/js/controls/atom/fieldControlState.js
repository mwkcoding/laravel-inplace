import { atom } from 'recoil';

const fieldControlState = atom({
    key: 'fieldControlState',
    default: {editing: false, save: false},
});

const fieldControlAppearState = atom({
    key: 'fieldControlAppearState',
    default: true,
});

export { fieldControlState, fieldControlAppearState };