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

#### __get_state

`exportify` does not automatically wrap properties inside the object, and this is fine in most
cases. But sometimes you want to export objects such as closures inside an object, or specify what to
export. in order to do that, you can implement `__get_state` on your object. This method should
return an array with the properties you want to restore with `__get_state`.

````php
class User {
    private $name;
    private $email;
    
    public function __construct($name, $email) {
        $this->name = $name;
        $this->email = $email;
    }
    
    public function __get_state(): array {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
    
    public static function __set_state($state): self {
        return new self($state['name'], $state['email']);
    }
}
````

Note: you do not need to use exportify here yourself, it is done automatically.

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

### var_export_file

`var_export_file` is the same as `var_export`, but it exports to a file instead of returning.

````php
use function Henzeb\VarExportWrapper\Support\Functions\var_export_file;

var_export_file('/tmp/config.php',[[fn()=>>true]]); // writes the var_export string to /tmp/config.php after wrapping closure

````
### var_import

`var_import` is useful when you want to import a var_exported string or file. This function
will automatically unwrap the `VarExportable` instances. You can also pass an array that was
imported in another way, but still contains `VarExportable` instances.

````php
use function Henzeb\VarExportWrapper\Support\Functions\var_import;
use function Henzeb\VarExportWrapper\Support\Functions\var_export;
 
var_import(var_export(fn()=>true)); // returns the closure
var_import('path/to/var_export.php'); // returns the object which is exported in the specified file
var_import([new \Henzeb\VarExportWrapper\VarExportable(fn()=>'hello')]); // returns the array with closure
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
