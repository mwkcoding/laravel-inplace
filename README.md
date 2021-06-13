# Laravel Inplace

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devsrv/laravel-inplace.svg?style=flat-square)](https://packagist.org/packages/devsrv/laravel-inplace)
[![Total Downloads](https://img.shields.io/packagist/dt/devsrv/laravel-inplace.svg?style=flat-square)](https://packagist.org/packages/devsrv/laravel-inplace)

Because forms are boaring.

This package provide set of blade components to make editing content exciting & blazing fast.

### Installation

```shell
composer require devsrv/laravel-inplace
```

### Setup (_required_)

include `@include('inplace::styles')` and `@include('inplace::scripts')` on every page where you'll be using the _inplace_ component

> its best to add those directives in your main layout blade file and extend that blade layout

```php
...
    @include('inplace::styles')
</head>
<body>
    ...

    @include('inplace::scripts')
</body>
</html>
```

### Publishing Frontend Assets (_required_)
```shell
php artisan vendor:publish --provider="devsrv\inplace\InplaceServiceProvider" --tag=public
```

##### 💡 NOTE :
> when updating the package make sure to use the `--force` flag to keep the assets up-to-date i.e. 
> 
> `php artisan vendor:publish --provider="devsrv\inplace\InplaceServiceProvider" --tag=public --force`

### Publish config (_optional_)

```shell
php artisan vendor:publish --provider="devsrv\inplace\InplaceServiceProvider" --tag=config
```
#### ✔️ icons :
svg content for `edit` `save` and `cancel` button icon

#### ✔️ middleware : 
add as many middlewares you with in the `middleware` array e.g.: `['auth', 'admin']`



## Guide

### Inline Editable [All Examples here](https://github.com/devsrv/laravel-inplace-example/blob/master/resources/views/welcome.blade.php)

**Example 1** | simplest usage

```php
<x-inplace-text
  model="App\Models\User:name,1"		// (REQUIRED) format: App\ModelNamespace\Model:id
  :model="\App\Models\User::find(1)" 	// Alternatively you can pass model instance
  column="name"							// (OPTIONAL) name of the table column to update
  validation="required|min:10"			// (OPTIONAL) pass validation rules
>
  {{ \App\Models\User::find(1)->name }}
</x-inplace-text>
```

**Example 2** | Slotted Markup

```php
<x-inplace-text
	:model="$user"
    ...
>
   <x-slot name="before"><div class="myclass anotherclass"><h2></x-slot>	// custom markup prepend
   <x-slot name="after"></h2></div></x-slot>								// custom markup append

    {{ $user->email }}
</x-inplace-text>
```

**Example - 3** | Pass Custom Class to save content
[check here](https://github.com/devsrv/laravel-inplace-example/blob/c81c21d76c888958964b1eb1a589ea524694f8e9/resources/views/welcome.blade.php#L40)

> the custom save class must be a invokable object (class with __invoke method) which receives `$model, $column, $value` and it should return an array with key `success` (bool) and `message` (string)


_Example Custom Save class :_ refer to this [CustomSave](https://github.com/devsrv/laravel-inplace-example/blob/master/app/Http/Inplace/Requests/CustomSave.php) full example

**Example - 4** | Render content as custom component

```php
<x-inplace-text
  model="..."
  render-as="CustomInlineRender"		// pass your own blade component which takes care of how content gets rendered
>
  ...
</x-inplace-text>
```

> make sure to pass `{{ $attributes }}` to the html elenent that is wrapping the target content
> e.g.: `<h1 class="your-class" {{ $attributes }}></h1>`

refer to this example [component](https://github.com/devsrv/laravel-inplace-example/blob/master/resources/views/components/custom-inline-render.blade.php)

**Example - 5** | Complex Validation Rules

```php
@php
$rules = serialize(['required', \Illuminate\Validation\Rule::in(['11', '12']), 'min:2']);  // make sure to serialize
@endphp

<x-inplace-text
model="App\Models\User:1"
column="name"
:validation="$rules"                   // complex validation can be passed by `serialize`
>
  {{ \App\Models\User::find(1)->name }}
</x-inplace-text>
```
refer [this example](https://github.com/devsrv/laravel-inplace-example/blob/c81c21d76c888958964b1eb1a589ea524694f8e9/resources/views/welcome.blade.php#L57)

#### 📌 Note: 
if using direct inplace blade component to pass all the configs, authorization will be enforced always. to override/customize this behaviour consider using [field maker](https://github.com/devsrv/laravel-inplace#advanced) configurator.


### 👾ADVANCED
instead passing config via attributes you can use the advanced field configurator file where you have access to fluent config setter methods, also this approach lets you reuse same config for multiple edits and more fine grained options to configure

```shell
php artisan inplace:config {all | text | relation}
```
this command will create `App\Http\Inplace\Text.php` and `App\Http\Inplace\Relation.php` or just one of them depending on your input

in the `config` method of the class add multiple configs as array 
> for inline field: using `devsrv\inplace\InlineText` class and 
> 
> for relation field: using `devsrv\inplace\RelationManager` class

then you can simply use the component as:
```php
<x-inplace-text
	id="USERNAME"							// the should match id of field config
	:model="\App\Models\User::find(1)"		// however model still needs to be passed via attribute (always required)
>
```

refer to [this file](https://github.com/devsrv/laravel-inplace-example/blob/master/app/Http/Inplace/Text.php) for example

> Field configurator supports some extra methods like: 
`authorizeUsing( closure )`, `bypassAuthorize()`, `middleware(['foo', 'bar'])` etc.

**_detailed documentation comming soon_ . . .**


### 🎁 Bonus:

#### 1. **authorize manually:** 
when passing custom class to save data you may choose to authorize the action from within your class using
   1. `Gate::authorize('update', $model);` OR
   2. `Gate::authorize('edit-settings');` OR
   3. `$this->authorize('update', $model);`

> donn't forget to use the `Illuminate\Foundation\Auth\Access\AuthorizesRequests` trait.

referer this [example](https://github.com/devsrv/laravel-inplace-example/blob/master/app/Http/Inplace/Requests/CustomSave.php) 

#### 2. if you use the popular [SPATIE PERMISSION](https://github.com/spatie/laravel-permission) package: 
you may choose to use `authorizeSpatieRoleOrPermission` method that comes with the package as a support when you use the `devsrv\inplace\Traits\SpatieAuthorize` trait.

```php
use devsrv\inplace\Traits\SpatieAuthorize;

class CustomSave
{
    use SpatieAuthorize;

    public function __invoke($model, $column, $value)
    {
    	$this->authorizeSpatieRoleOrPermission(['admin', 'some permission']);

        // save data here

        return [
            'success' => 0,
            'message' => 'not allowed'
        ];
    }
}
```

refer this [example](https://github.com/devsrv/laravel-inplace-example/blob/c81c21d76c888958964b1eb1a589ea524694f8e9/app/Http/Inplace/Requests/CustomSave.php#L32)

#### 2. 🔥 Listen events
1. `inplace-editable-progress` custom `window` browser event diaptched after ajax start & ajax finished. refer to [example](https://github.com/devsrv/laravel-inplace-example/blob/3057161a1af84a2f9a9c215157f0e28c9edcb1c4/resources/views/app.blade.php#L58) for [NProgress](https://github.com/rstacruz/nprogress) Implementation
2. `inplace-editable-finish` custom `window` browser event diaptched after content is either saved | failed by server. refer to [example](https://github.com/devsrv/laravel-inplace-example/blob/3057161a1af84a2f9a9c215157f0e28c9edcb1c4/resources/views/app.blade.php#L49) for a sample notifier system

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👋🏼 Say Hi! 
Leave a ⭐ if you find this package useful 👍🏼,
don't forget to let me know in [Twitter](https://twitter.com/srvrksh)  
