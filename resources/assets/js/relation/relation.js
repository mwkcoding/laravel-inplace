import React, { useState, useEffect, useRef } from 'react';
import { useRecoilState, useRecoilValue } from 'recoil';
import { fieldControlState } from './../controls/atom/fieldControlState';
import { fieldValuesState } from './recoil/atom/editorStates';
import BasicCheckbox from './fields/checkbox';

export default function Relation(props) {
    const skipPropsPass = ['model', 'relationName', 'relColumn', 'renderTemplate', 'currentValues', 'signature'];

    const [error, setError] = useState({has: false, type: '', message: ''});
    const [success, setSuccess] = useState(false);
    const [validationErrors, setValidationErrors] = useState([]);
    const [saving, setSaving] = useState(false);
    const [throttled, setThrottled] = useState(0);

    const [control, setControl] = useRecoilState(fieldControlState);
    const [fieldValues, setFieldValues] = useRecoilState(fieldValuesState);

    const firstMounded = useRef(true);

    useEffect(() => {
        if (firstMounded.current) {
            let values = [];

            if(fieldValues.last.length > 0) {
                values = fieldValues.last;
            }
            else {
                values = ! props.multiple ? props.currentValues.slice(0, 1) : props.currentValues;
            }

            setFieldValues({last: values, current: values});
            firstMounded.current = false;
        }
    }, [])

    useEffect(() => {
        if(success) dispatchSuccessEvent();
    }, [success]);
    
    useEffect(() => {
        if(control.save) {
            handleSave()
            .then(() => {
                setControl( (prevState) => ({...prevState, save: false }) );
            });

            return;
        }
    }, [control.save]);

    useEffect(() => {
        if(error.has)
        setFieldValues((prevValues) => ({...prevValues, current: fieldValues.last })); 
    }, [error.has]);
    
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

    const handleSave = () => {
        return new Promise((resolve) => {
            resetFormStates();

            window.dispatchEvent(new CustomEvent("inplace-editable-progress", {
                detail: { start: true }
            }));

            let errorTypeText = '';

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
                    inplace_field_sign: props.signature,
                    values: fieldValues.current,
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
                const errorTypes = [
                    {code: 422, type: 'Validation Error !'},
                    {code: 403, type: 'Permission restricted !'},
                    {code: 401, type: 'Unauthorized !'},
                    {code: 429, type: 'Too many requests !'},
                    {code: 500, type: 'Server Error !'},
                    {code: 503, type: 'Server Error !'}
                ];
                
                const err = errorTypes.find((err) => err.code === res.status);
                errorTypeText = typeof err !== 'undefined' ? err.type : 'Error saving content !';

                if(res.headers.get('Retry-After')) setThrottled(res.headers.get('Retry-After'));

                return res.json();
            })
            .then(result => {
                if(Object.prototype.hasOwnProperty.call(result, 'success') && Number(result.success) === 1) {
                    setSuccess(true);

                    setFieldValues((prevValues) => ({...prevValues, last: fieldValues.current}));

                    if(Object.prototype.hasOwnProperty.call(result, 'redner') && result.redner)
                    document.getElementById(props.contentId).innerHTML = result.redner;
                    
                    return;
                }

                setError((prevErr) => {
                    return {
                        has: true,
                        type: errorTypeText || 'Error saving content !',
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

            resolve(true);
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
            <BasicCheckbox {...fieldOptions} />

            <div className="message">
                <span>throttled for {throttled}</span>

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