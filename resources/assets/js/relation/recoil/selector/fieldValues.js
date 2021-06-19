import { selector, selectorFamily } from 'recoil';
import { fieldValuesState } from '../atom/editorStates';

const resetCurrentFieldValues = selectorFamily({
    key: 'resetCurrentFieldValues',
    get: multiple => ({get}) => {
        const {last, current} = get(fieldValuesState);
        
        if(! multiple && last.length > 1) {
            return last.slice(0, 1);
        }

        return last;
    },
});

export { resetCurrentFieldValues };