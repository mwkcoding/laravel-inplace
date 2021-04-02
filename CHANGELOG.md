# Changelog

## 1.0.0 - 2021-03-21

- initial release

## 1.1.0 - 2021-03-28

- removed livewire
- custom `saveusing` & `model` attribute data encrypted using OpenSSL and the AES-256-CBC cipher
- after data save `inplace-editable-finish` event dispatched by inplace with property `success : true|false`
- data save start & end track ajax progress by listening `inplace-editable-progress` event

## 1.1.1 - 2021-04-02

- using webpack bundled script instead inline
- alpine used as npm module
- no conflict with user's alpine / alpine magic helpers as all inplace script is bundled
- lottie animation support for success notification
