# :package_description

[![Latest Version on Packagist](https://img.shields.io/packagist/v/:vendor_name/:package_name.svg?style=flat-square)](https://packagist.org/packages/:vendor_name/:package_name)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/:vendor_name/:package_name/run-tests?label=tests)](https://github.com/:vendor_name/:package_name/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/:vendor_name/:package_name/Check%20&%20fix%20styling?label=code%20style)](https://github.com/:vendor_name/:package_name/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/:vendor_name/:package_name.svg?style=flat-square)](https://packagist.org/packages/:vendor_name/:package_name)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

### Installation

```shell
composer require devsrv/laravel-inplace
```

### setup

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

### 💡 NOTE

inplace uses [Livewire](https://laravel-livewire.com/) and [Alpine JS](https://github.com/alpinejs/alpine) internally, so if you are using any of these in your application then follow the below setup method or else you'll get conflict errors

#### if you are using Livewire in your application

- after `@livewireStyles` add the `@stack('inplace.component.style')` directive for inplace's styles to be pushed properly.

- after `@livewireScripts` add the below scripts in the given order

  1.  `<script src="https://cdn.jsdelivr.net/gh/alpine-collective/alpine-magic-helpers@1.0.0/dist/index.min.js"></script>`
  2.  `<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.1/dist/alpine.min.js" defer></script>`

- add a `@stack('inplace.component.script')` directive for inplace scripts to be pushed properly

#### if you are using Alpine JS in your application

- add the alpine magic helpers script before alpine -
  1.  `<script src="https://cdn.jsdelivr.net/gh/alpine-collective/alpine-magic-helpers@1.0.0/dist/index.min.js"></script>`

### Publish config

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

### Bonus:

1. **authorize manually:** when passing custom class to save data you may choose to authorize the action from within your class using
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

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
