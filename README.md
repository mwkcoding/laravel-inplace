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
php artisan vendor:publish --tag=public
```

##### 💡 NOTE :
> when updating the package make sure to use the `--force` flag to keep the assets up-to-date i.e. 
`php artisan vendor:publish --tag=public --force`

### Publish config (_optional_)

`php artisan vendor:publish --provider="devsrv\inplace\InplaceServiceProvider"`

this file includes an global `authorize` config value. setting this `true` will enforce laravel's policy authorization for all the inplace edit components, though you can override the global behaviour by passing an `authorize` (bool) attribute to your inplace component

```php
<x-inplace-component
   model="App\Models\User:email,1"
   :authorize="false"	// override global authorization config
   inline
>
  Content to edit
</x-inplace-component>
```

## Guide

### Inline Editable [All Examples here](https://github.com/devsrv/laravel-inplace-example/blob/master/resources/views/welcome.blade.php)

**Example 1** | simplest usage

```php
<x-inplace-component
  model="App\Models\User:name,1"	// (OPTIONAL) format: App\ModelNamespace\Model:column,id
  validation="required|min:10"		// (OPTIONAL) pass validation rules
  inline
>
  {{ \App\Models\User::find(1)->name }}
</x-inplace-component>
```

**Example 2** | Slotted Markup

```php
<x-inplace-component
	model="App\Models\User:email,1"
	validation="required|email"
	:authorize="false"
    :inline="true"
>
   <x-slot name="before"><div class="myclass anotherclass"><h2></x-slot>	// custom markup prepend
   <x-slot name="after"></h2></div></x-slot>								// custom markup append

    {{ \App\Models\User::find(1)->email }}
</x-inplace-component>
```

**Example - 3** | Pass Custom Class to save content

```php
<x-inplace-component
  value="content to update"					// you may choose to pass the content using the value attribute
  saveusing="App\Http\Inplace\CustomSave"	// pass your custom class which takes care of saving the content
  inline
/>
```

> the custom save class must consist a `public save` method which receives `$model, $column, $value` and it should return an array with key `success` (bool) and `message` (string)

👉 you might have noticed we didn't use the `model` attribute as because we choosed to take care of saving the data by using our own class, though if you want you can still use the model attribute to pass data which you'll get as parameter inside the `save` method of your custom class - [example](https://github.com/devsrv/laravel-inplace-example/blob/9f6961485e8c6488e6ffa56c9ebb4e45686937ce/app/Http/Inplace/CustomSave.php#L12)

_Example Custom Save class :_

```php
namespace App\Http\Inplace;

class CustomSave
{
    public function save($model, $column, $value)
    {
        // save data here
        $model->{$column} = $value;
        if($model->save()) {
            return [
                'success' => 1,
                'message' => 'saved successfully'
            ];
        }

        return [
            'success' => 0,
            'message' => 'failed to save'
        ];
    }
}
```

refer to this [CustomSave](https://github.com/devsrv/laravel-inplace-example/blob/master/app/Http/Inplace/CustomSave.php) full example

**Example - 4** | Render content as custom component

```php
<x-inplace-component
  model="App\Models\Post:title,1"
  render-as="CustomInlineRender"		// pass your own blade component which takes care of how content gets rendered
  inline
>
  {{ \App\Models\Post::find(1)->title }}
</x-inplace-component>
```

> make sure to pass `{{ $attributes }}` to the html elenent that is wrapping the target content
> e.g.: `<h1 class="your-class" {{ $attributes }}></h1>`

refer to this example [component class](https://github.com/devsrv/laravel-inplace-example/blob/master/app/View/Components/CustomInlineRender.php) & [component view](https://github.com/devsrv/laravel-inplace-example/blob/master/resources/views/components/custom-inline-render.blade.php)

**Example - 5** | Complex Validation Rules

```php
@php
$rules = serialize(['required', \Illuminate\Validation\Rule::in(['11', '12']), 'min:2']);  // make sure to serialize
@endphp

<x-inplace-component
 inline
model="App\Models\User:name,1"
:validation="$rules"                   // complex validation can be passed by `serialize`
>
  {{ \App\Models\User::find(1)->name }}
</x-inplace-component>
```
refer [this example](https://github.com/devsrv/laravel-inplace-example/blob/3057161a1af84a2f9a9c215157f0e28c9edcb1c4/resources/views/welcome.blade.php#L33)

### Bonus:

#### 1. **authorize manually:** 
when passing custom class to save data you may choose to authorize the action from within your class using
   1. `Gate::authorize('update', $model);` OR
   2. `Gate::authorize('edit-settings');` OR
   3. `$this->authorize('update', $model);`

> donn't forget to use the `Illuminate\Foundation\Auth\Access\AuthorizesRequests` trait.

referer this [example](https://github.com/devsrv/laravel-inplace-example/blob/9f6961485e8c6488e6ffa56c9ebb4e45686937ce/app/Http/Inplace/CustomSave.php#L20) 2. **if you use the popular [SPATIE PERMISSION](https://github.com/spatie/laravel-permission) package:** you may choose to use `authorizeSpatieRoleOrPermission` method that comes with the package as a support when you use the `devsrv\inplace\Traits\SpatieAuthorize` trait.

```php
use devsrv\inplace\Traits\SpatieAuthorize;

class CustomSave
{
    use SpatieAuthorize;

    public function save($model, $column, $value)
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

refer this [example](https://github.com/devsrv/laravel-inplace-example/blob/9f6961485e8c6488e6ffa56c9ebb4e45686937ce/app/Http/Inplace/CustomSave.php#L30)

#### 2. Listen events
1. `inplace-editable-progress` custom `window` browser event diaptched after ajax start & ajax finished. refer to [example](https://github.com/devsrv/laravel-inplace-example/blob/3057161a1af84a2f9a9c215157f0e28c9edcb1c4/resources/views/app.blade.php#L58) for [NProgress](https://github.com/rstacruz/nprogress) Implementation
2. `inplace-editable-finish` custom `window` browser event diaptched after content is either saved | failed by server. refer to [example](https://github.com/devsrv/laravel-inplace-example/blob/3057161a1af84a2f9a9c215157f0e28c9edcb1c4/resources/views/app.blade.php#L49) for a sample notifier system

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👋🏼 Say Hi! 
Leave a ⭐ if you find this package useful 👍🏼,
don't forget to let me know in [Twitter](https://twitter.com/srvrksh)  
