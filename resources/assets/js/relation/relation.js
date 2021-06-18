import React, { useState, useEffect, useCallback, useRef } from 'react';
import BasicCheckbox from './fields/checkbox';

export default function Relation(props) {
    const skipPropsPass = ['model', 'relationName', 'relColumn', 'renderTemplate', 'fieldId'];

    const [error, setError] = useState({has: false, type: '', message: ''});
    const [success, setSuccess] = useState(false);
    const [validationErrors, setValidationErrors] = useState([]);
    const [input, setInput] = useState(null);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if(success) dispatchSuccessEvent();
    }, [success]);
    
    useEffect(() => {
        if(props.save) {
            handleSave();

            props.onSaveFinished();

            return;
        }
    }, [props.save]);

    const dispatchSuccessEvent = () => {
        window.dispatchEvent(new CustomEvent("inplace-editable-finish", {
            detail: { success: true }
        }));
    }

    const dispatchErrorEvent = () => {
        window.dispatchEvent(new CustomEvent("inplace-editable-finish", {
            detail: { success: false }
        }));
    }

    const resetFormStates = () => {
        setSaving(true);
        setValidationErrors([]);
        setError({has: false, message: ''});
        setSuccess(false);
    }

    const handleInputChange = useCallback((value) => setInput(value), []);

    const handleSave = () => {
        resetFormStates();

        window.dispatchEvent(new CustomEvent("inplace-editable-progress", {
            detail: { start: true }
        }));

        fetch(window._inplace.relation.route, {
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-Token": window._inplace.csrf_token
            },
            method: 'POST',
            credentials: "same-origin",
            body: JSON.stringify({
                values: input,
                id: props.id,
                model: props.model,
                rules: props.rules,
                eachRules: props.eachRules,
                relationName: props.relationName,
                relColumn: props.relColumn,
                renderTemplate: props.renderTemplate,
            })
        })
        .then(res => {
            if(res.status === 422) setError({has: true, type: 'Validation Error !', message: ''});
            else if(res.status === 403) setError({has: true, type: 'Permission restricted !', message: ''});
            else if(res.status >= 500) setError({has: true, type: 'Server Error !', message: ''});

            return res.json();
        })
        .then(result => {
            if(Object.prototype.hasOwnProperty.call(result, 'success') && Number(result.success) === 1) {
                setSuccess(true);

                if(Object.prototype.hasOwnProperty.call(result, 'redner') && result.redner)
                document.getElementById(props.contentId).innerHTML = result.redner;
                
                return;
            }

            setError((prevErr) => {
                return {
                    has: true,
                    type: prevErr.type || 'Error saving content !',
                    message: Object.prototype.hasOwnProperty.call(result, 'message') && ' '+ result.message 
                }
            });

            dispatchErrorEvent();

            // if validation error show em all
            if(Object.prototype.hasOwnProperty.call(result, 'errors')) {
                setValidationErrors(Object.entries(result.errors).map(err => err[1]));
            }

            return;
        })
        .catch(err => {
            setError({has: true, type: 'Network request failed !', message: 'Error saving content !'});

            dispatchErrorEvent();
        })
        .finally(() => {
            setSaving(false);

            window.dispatchEvent(new CustomEvent("inplace-editable-progress", {
                detail: { stop: true }
            }));
        });

    }

    const fieldOptions = {...Object.keys(props).filter(key => ! skipPropsPass.includes(key))
        .reduce((obj, key) => {
            obj[key] = props[key];
            return obj;
        }, {})
    }

    return (
        <div>
            <BasicCheckbox {...fieldOptions} onInputChange={handleInputChange} hasError={error.has} />

            <div className="message">
                {! saving ?
                    error.has && (<><h2 className="text-danger">{error.type}</h2><span className="text-danger">{error.message}</span></>) : null
                }

                { (! saving && validationErrors.length > 0) &&
                    validationErrors.map((e, i) => <p key={i} className="text-danger">{e}</p>)
                }

                {! saving ?
                    success && (<span className="text-success">Success</span>) : null
                }
            </div>
        </div>
    );
}