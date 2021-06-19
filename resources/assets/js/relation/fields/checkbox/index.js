import React, { useState, useEffect } from 'react';

function BasicCheckbox(props) {
    const { hash, thumbnailed, thumbnailWidth, currentValues, onInputChange, multiple, hasError } = props;

    const optionsBank = window._inplace.options.relation.find(opt => opt.id === hash);
    const options = optionsBank ? optionsBank.options : [];

    const resetSelection = () => {
        if(! multiple && currentValues.length > 1) {
            return currentValues.slice(0, 1);
        }

        return currentValues;
    }

    const [selected, setSelected] = useState(() => resetSelection());

    useEffect(() => {
        if(hasError) 
        setSelected(resetSelection());
    }, [hasError]);

    useEffect(() => {
        onInputChange(selected);
    }, [selected]);

    const handleChange = (e) => {
        const value = Number(e.target.value);

        if(! multiple) {
            if(selected.includes(value)) {
                setSelected([]);
            } else {
                setSelected([value]);
            }

            return;
        }

        if(e.target.checked) {
            if(! selected.includes(value)) {
                setSelected(prevIds => [...prevIds, value]);

                return;
            }

            return;
        }

        const optionIndex = selected.findIndex(id => id === value);

        setSelected(
            prevIds => [
                ...prevIds.slice(0, optionIndex), 
                ...prevIds.slice(optionIndex + 1)
            ]
        );
    }

    return (
        <div>
            <ul>
            { options.map((opt) => 
                (<li key={opt.value}>
                    {thumbnailed &&
                        <img src={opt.thumbnail} width={thumbnailWidth} alt="avatar" />
                    }

                    <input type="checkbox" value={opt.value} onChange={handleChange} checked={selected.includes(opt.value)} />
                    <label>{opt.label}</label>
                </li>)
            )}
            </ul>
        </div>
    )
}

export default React.memo(BasicCheckbox);