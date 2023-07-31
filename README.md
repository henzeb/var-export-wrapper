# var_export wrapper

[![Latest Version on Packagist](https://img.shields.io/packagist/v/henzeb/var-export-wrapper.svg?style=flat-square)](https://packagist.org/packages/henzeb/var-export-wrapper)
[![Total Downloads](https://img.shields.io/packagist/dt/henzeb/var-export-wrapper.svg?style=flat-square)](https://packagist.org/packages/henzeb/var-export-wrapper)

var_export is a good choice when caching config. This is how it is done in
Laravel for example. But not every object is automatically exportable.

Imagine you want to make an `ImageManipulationService` that resizes your images.
You want it to be configurable, so you get something like this:

````php
return [
    [
        'suffix' => 'thumb',
        'width' => 300,
        'height' => 200,
        'ratio' => true,
        'pixelate' => 3,
    ]
    // ...
]
````

You would need an option for each possible feature, or implement the new
wanted feature whenever requested. You could work with invokable classes,
but that won't make it readable.

What if you could do this:

````php
return [
     'constraints' => [
        'aspectRatio' => fn(Constraint $constraint) => $constraint->aspectRatio()
    ],
    'images' => [
        'suffix' => 'thumb',
        'manipulate' => function (Image $image) {
                $image->resize(
                    300,
                    20,
                    config('constraints.aspectRatio')
                )->pixelate(3);
            }
        }
    ]
    // ...
]
````

With this package, you can export closures and objects that do not implement
`__set_state`. You do not need laravel to use it, but when you do, it automatically
parses your configuration for `artisan config:cache`

## Installation

Just install with the following command.

```bash
composer require henzeb/var-export-wrapper
```

## Usage

### exportify

`exportify` is what wraps the object or an array of objects 
````php
use function Henzeb\VarExportWrapper\Support\Functions\exportify;

exportify(fn()=>true); // returns instance of VarExportable
exportify(new ExportableClass()); // returns instance of ExportableClass
exportify(new RegularClass()); // returns instance of VarExportable

exportify(['recursive' => [new RegularClass(), fn()=>true]]); // returns nested array with 2 VarExportable instances
````

Note: `exportify` also iterates through objects implementing `Traversable` or `ArrayAccess`.

## is_exportable

Validates the given object or array. If not exportable, it returns false.

````php
use function Henzeb\VarExportWrapper\Support\Functions\is_exportable;

is_exportable(fn()=>true); // returns false
is_exportable(new RegularClass()); // returns false
is_exportable(new ExportableClass()); // returns true
is_exportable(STDIN); // returns true
is_exportable([[fn()=>true]]); // returns false
is_exportable([[new ExportableClass()]]); // returns true
is_exportable([[new ExportableClass(), fn()=>true]]); // returns false
````

### var_export

`var_export` is the supercharged version of the native function, but
under the hood it will automatically wrap everything that is not exportable
by default in a `VarExportable` instance, before actually exporting the value.

````php
use function Henzeb\VarExportWrapper\Support\Functions\var_export;

var_export(fn()=>true); // dumps the var_export string after wrapping the closure.
var_export(new RegularClass()); // dumps the var_export string after wrapping the object
var_export(new ExportableClass()); // dumps the var_export string without wrapping
var_export([[fn()=>>true]]); // dumps the var_export string after wrapping closure

var_export(fn()=>true, true); // returns the var_export string after wrapping the closure.
var_export(new RegularClass(), true); // returns the var_export string after wrapping the object
var_export(new ExportableClass(), true); // returns the var_export string without wrapping
var_export([[fn()=>true]], true); // returns the var_export string after wrapping closure
 
````

### Laravel Config

When installed in a Laravel installation, you can just start using closures and objects
inside your configuration. When calling `artisan config:cache`, var_export wrapper automatically
wraps them in a wrapper.

### Closures under the hood

To be able to export closures, it has to serialize them. It uses
[laravel/serializable](https://github.com/laravel/serializable-closure)
to achieve that. This means that if you've set a secret key, the closure
is signed, otherwise it's natively serialized and thus unsigned.

You do not need to wrap closures before passing them to `exportify`

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email henzeberkheij@gmail.com instead of using the issue tracker.

## Credits

- [Henze Berkheij](https://github.com/henzeb)

## License

The GNU AGPLv. Please see [License File](LICENSE.md) for more information.
]()
