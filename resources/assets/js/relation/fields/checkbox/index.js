import React, { useState, useEffect } from 'react';

function BasicCheckbox(props) {
    const { options, thumbnailed, thumbnailWidth, currentValues, multiple, onSave, hasError } = props;

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
    }, [hasError])

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

    const handleSave = () => {
        onSave(selected);
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

            <button type="button" onClick={handleSave} className="btn">save</button>
        </div>
    )
}

export default React.memo(BasicCheckbox);