import 'alpinejs';
import lottieWeb from 'lottie-web';
import lottieCheck from './../../lottie/check-okey-done.json';
import lottieError from './../../lottie/error-cross.json';

window.inlineEditable = function () {
    return {
        editing: false,
        saving: false,
        error: false,
        success: false,
        errorMessage: '',
        validationErrors: [],
        lottie: null,
        animatingNotify: false,
        onBoot(watch) {
            watch('error', has => {
                if(has) {
                    window.dispatchEvent(new CustomEvent("inplace-editable-finish", {
                        detail: { success: false }
                    }));

                    setTimeout(() => this.error = false, 1500);
                }
            });

            watch('success', has => {
                if(has) {
                    window.dispatchEvent(new CustomEvent("inplace-editable-finish", {
                        detail: { success: true }
                    }));

                    setTimeout(() => this.success = false, 1500);
                }
            });
        },
        initEdit() {
            this.editing = true;
            this.errorMessage = '';
            this.validationErrors = [];

            this.$nextTick(() => {
                this.$refs.field.focus();
                const range = document.createRange();
                const sel = window.getSelection();

                range.setStart(this.$refs.field, 1);
                range.collapse(true);

                sel.removeAllRanges();
                sel.addRange(range);
            });
        },
        playLottie(type = 'success') {
            this.animatingNotify = true;

            return new  Promise(resolve => {
                let lottieAnimate = type === 'success' ? lottieCheck : lottieError;

                this.lottie = lottieWeb.loadAnimation({
                    container: this.$refs['lottie-anim'],
                    animationData: lottieAnimate,
                    renderer: 'svg',
                    loop: false,
                    autoplay: true,
                    rendererSettings: {
                        className: type !== 'success' ? 'lottie-sm' : '',
                    }
                });

                this.$nextTick(() => {
                    this.lottie.addEventListener('complete', () => {
                        this.lottie.destroy();

                        this.animatingNotify = false;

                        resolve();
                    });
                });
            });
        },
        handleSave() {
            window.dispatchEvent(new CustomEvent("inplace-editable-progress", {
                detail: { start: true }
            }));

            this.editing = false;
            this.errorMessage = '';
            this.validationErrors = [];
            this.saving = true;

            fetch(window._inplace.route, {
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-Token": window._inplace.csrf_token
                },
                method: 'POST',
                credentials: "same-origin",
                body: JSON.stringify({
                    content: this.editedContent,
                    authorize: this.authorize,
                    model: this.model,
                    column: this.column,
                    rules: this.rules,
                    saveusing: this.saveusing,
                })
            })
            .then(res => {
                if(res.status === 422) this.errorMessage = 'Validation Error !';
                else if(res.status === 403) this.errorMessage = 'Permission restricted !';
                else if(res.status >= 500) this.errorMessage = 'Error saving content !';

                return res.json();
            })
            .then(result => {
                this.saving = false;

                if(Object.prototype.hasOwnProperty.call(result, 'success') && Number(result.success) === 1) {
                    this.success = true;
                    this.content = this.editedContent;

                    this.playLottie('success').then(() => {

                    });
                    
                    return;
                }

                this.error = true;
                this.editedContent = this.content;

                this.errorMessage += Object.prototype.hasOwnProperty.call(result, 'message') && ' '+ result.message;

                // if validation error show em all
                if(Object.prototype.hasOwnProperty.call(result, 'errors'))
                this.validationErrors = Object.entries(result.errors)[0][1];

                this.playLottie('error').then(() => {
                });

                return;
            })
            .catch(err => {
                this.saving = false;
                this.error = true;
                this.editedContent = this.content;
                this.errorMessage = 'Error saving content !';

                this.playLottie('error').then(() => {
                });
            })
            .finally(() => {
                window.dispatchEvent(new CustomEvent("inplace-editable-progress", {
                    detail: { stop: true }
                }));
            });
        },
        handleCancel() {
            this.editing = false;
            this.editedContent = this.content;
        },
        trackEdit(ev) {
            this.editedContent = ev.target.innerText;
        }
    }
}