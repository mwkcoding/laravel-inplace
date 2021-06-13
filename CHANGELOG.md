# Changelog

## 2.0.0 - 2021-06-13

#### Added
    - new methods to customize authorization behavioir for text component - `authorizeUsing`, `bypassAuthorize` 
    - relation component memoization for options list
    - relation component do not re-query options if relation already eager loaded
    - if data save error then relation options selection revert back
    - relation component replace current values markup after successful data save

#### Removed
    - global authorize config removed. if using blade component attribute to pass all configs then authorization will always be enforced
    - `authorize` `withoutMiddleware` config methods removed from text component 
    - `authorize` attribute no longer supported for text component

#### Changed
    - `x-inplace-inline` to `x-inplace-text`
    - `saveusing` renammed to `save-using`
    - custom save-as class support invokable object

## 1.3.1 - 2021-04-25

- edit control button appear on content hover
- config support for buttons custom svg icons
- inline editable text doubleclick support

## 1.3.0 - 2021-04-24

- instead of serviceprovider for advanced configuration now using seperate configurator files [ App\Http\Inplace\Inline.php App\Http\Inplace\Relation.php ]
- publish advanced config using: `php artisan inplace:config`
- `inplace:config` command support arguments - `all` `inline` `relation`

## 1.2.0 - 2021-04-18

inline edit
- vendor publish tag support [`public`, `config`] `php artisan vendor:publish --provider="devsrv\inplace\InplaceServiceProvider" --tag=public`
- inline edit component name changed from `x-inplace-component` to `x-inplace-inline`
- changed `authorize` attribute behaviour [true - user authorized, false - user not authorized]
- model attribute input format changed [ namespace\Model\Class:primaryKeyValue - ex. `App\Models\User:1` ]
- model attribute support model instance too
- added column attribute [the column name to update]
- advanced field configuration support via fluent setter methods
    - publish using: `php artisan inplace:config`
    - register `App\Providers\InplaceConfigServiceProvider::class` in `app.php`

## 1.1.3 - 2021-04-04

- support for middlewares via published config
- lottie animation for success & failed saving states
- error messages notification & validation errors list when 422

## 1.1.2 - 2021-04-04

- lottie json path missing fix

## 1.1.1 - 2021-04-02

- using webpack bundled script instead inline
- alpine used as npm module
- no conflict with user's alpine / alpine magic helpers as all inplace script is bundled
- lottie animation support for success notification

## 1.1.0 - 2021-03-28

- removed livewire
- custom `saveusing` & `model` attribute data encrypted using OpenSSL and the AES-256-CBC cipher
- after data save `inplace-editable-finish` event dispatched by inplace with property `success : true|false`
- data save start & end track ajax progress by listening `inplace-editable-progress` event

## 1.0.0 - 2021-03-21

- initial release