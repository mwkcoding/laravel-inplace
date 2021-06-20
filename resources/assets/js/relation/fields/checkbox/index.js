import React, { useEffect, useRef } from 'react';
import { useRecoilState, useRecoilValue } from 'recoil';
import { fieldValuesState } from './../../recoil/atom/editorStates';

function BasicCheckbox(props) {
    const { hash, thumbnailed, thumbnailWidth, multiple } = props;

    const optionsBank = window._inplace.options.relation.find(opt => opt.id === hash);
    const options = optionsBank ? optionsBank.options : [];

    const [{last, current}, setFieldValues] = useRecoilState(fieldValuesState);

    const handleChange = (e) => {
        const value = Number(e.target.value);

        if(! multiple) {
            if(current.includes(value)) {
                setFieldValues((prevValues) => ({...prevValues, current: []}));
            } else {
                setFieldValues((prevValues) => ({...prevValues, current: [value]}));
            }

            return;
        }

        if(e.target.checked) {
            if(! current.includes(value)) {
                setFieldValues((prevValues) => ({...prevValues, current: [...prevValues.current, value]}));

                return;
            }

            return;
        }

        const optionIndex = current.findIndex(id => id === value);

        setFieldValues((prevValues) => ({...prevValues, current: [
            ...prevValues.current.slice(0, optionIndex), 
            ...prevValues.current.slice(optionIndex + 1)
        ]}));
    }

    return (
        <div>
            <ul>
            { options.map((opt) => 
                (<li key={opt.value}>
                    {thumbnailed &&
                        <img src={opt.thumbnail} width={thumbnailWidth} alt="avatar" />
                    }

                    <input type="checkbox" value={opt.value} onChange={handleChange} checked={current.includes(opt.value)} />
                    <label>{opt.label}</label>
                </li>)
            )}
            </ul>
        </div>
    )
}

export default React.memo(BasicCheckbox);