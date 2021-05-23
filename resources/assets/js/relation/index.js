import React from 'react';
import ReactDOM from 'react-dom'
import BasicCheckbox from './fields/checkbox';

export default function RelationEditor(props) {
    const skipPropsPass = ['model', 'relationName'];

    const handleSave = (values) => {
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
                values: values,
                model: props.model
            })
        })
        .then(res => {
            /*if(res.status === 422) this.errorMessage = 'Validation Error !';
            else if(res.status === 403) this.errorMessage = 'Permission restricted !';
            else if(res.status >= 500) this.errorMessage = 'Error saving content !';*/

            return res.json();
        })
        .then(result => {
            /*this.saving = false;

            if(Object.prototype.hasOwnProperty.call(result, 'success') && Number(result.success) === 1) {
                this.success = true;
                
                return;
            }

            this.error = true;

            this.errorMessage += Object.prototype.hasOwnProperty.call(result, 'message') && ' '+ result.message;

            // if validation error show em all
            if(Object.prototype.hasOwnProperty.call(result, 'errors'))
            this.validationErrors = Object.entries(result.errors)[0][1];*/

            return;
        })
        .catch(err => {
            /*this.saving = false;
            this.error = true;
            this.errorMessage = 'Error saving content !';*/
        })
        .finally(() => {
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
            <BasicCheckbox {...fieldOptions} onSave={handleSave}  />
        </div>
    );
}

window.drawRelationEditable = function(id, props) {
    ReactDOM.render(
        <RelationEditor {...props} />,
        document.getElementById(id)
    );
}