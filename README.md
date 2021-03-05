# Monkey Patch

This package is a standalone package of [ci-phpunit-test](https://github.com/kenjis/ci-phpunit-test) 's Monkey Patching.

This provides four monkey patchers.

- `ExitPatcher`: Converts `exit()` to Exception
- `FunctionPatcher`: Patches Functions
- `MethodPatcher`: Patches Methods in User-defined Classes
- `ConstantPatcher`: Changes Constant Values

## Table of Contents

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
  - [Configure](#configure)
  - [Convert `exit()` to Exception](#convert-exit-to-exception)
  - [Patch Functions](#patch-functions)
    - [Change Return Value](#change-return-value)
    - [Patch Other Functions](#patch-other-functions)
  - [Patch Methods in User-defined Classes](#patch-methods-in-user-defined-classes)
  - [Patch Constants](#patch-constants)
- [Class Reference](#class-reference)
- [License](#license)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Requirements

- PHP 7.3 or later

## Installation

```sh-session
$ composer require --dev kenjis/monkey-patch
```

## Usage

**Note:** The line number when an error occurs is probably different from the actual source code. Please check the cache file of the source that Monkey Patching creates.

**Note:** Using this package has a negative impact on speed of tests.

### Configure

- To enable monkey patching, add [the content of the bootstrap](https://github.com/kenjis/monkey-patch/blob/1.x/src/bootstrap.php) in your PHPUnit bootstrap file.
  - Set [MonkeyPatchManager::init()](https://github.com/kenjis/monkey-patch/blob/f5b1839a01c0c3cd56f4873e8c307b0583a5526b/src/bootstrap.php#L31-L61) arguments.
- To verify invocations, use the [MonkeyPatchTrait](https://github.com/kenjis/monkey-patch/blob/1.x/src/Traits/MonkeyPatchTrait.php) in your TestCase class.

### Convert `exit()` to Exception

This patcher converts `exit()` or `die()` statements to exceptions on the fly.

If you have a controller like below:

~~~php
    public function index()
    {
        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(['foo' => 'bar']))
            ->_display();
        exit();
    }
~~~

A test case could be like this:

~~~php
    public function test_index()
    {
        try {
            $this->request('GET', 'welcome/index');
        } catch (ExitException $e) {
            $output = ob_get_clean();
        }
        
        $this->assertContains('{"foo":"bar"}', $output);
    }
~~~

### Patch Functions

This patcher allows replacement of global functions that can't be mocked by PHPUnit.

But it has a few limitations. Some functions can't be replaced and it might cause errors.

So by default we can replace only a dozen pre-defined functions in [FunctionPatcher](https://github.com/kenjis/monkey-patch/blob/a11e1f227234dadeae2460d29b9c8ca6e91c88de/src/Patcher/FunctionPatcher.php#L31-L49).

~~~php
    public function test_index()
    {
        MonkeyPatch::patchFunction('mt_rand', 100, 'Welcome::index');
        
        $output = $this->request('GET', 'welcome/index');
        
        $this->assertContains('100', $output);
    }
~~~

`MonkeyPatch::patchFunction()` replaces PHP native function `mt_rand()` in `Welcome::index` method, and it will return `100` in the test method.

**Note:** If you call `MonkeyPatch::patchFunction()` without 3rd argument, all the functions (located in `include_paths` and not in `exclude_paths`) called in the test method will be replaced. So, for example, a function in CodeIgniter code might be replaced and it results in unexpected outcome.

#### Change Return Value

You could change return value of patched function using PHP closure:

~~~php
        MonkeyPatch::patchFunction(
            'function_exists',
            function ($function) {
                if ($function === 'random_bytes') {
                    return true;
                } elseif ($function === 'openssl_random_pseudo_bytes') {
                    return false;
                } elseif ($function === 'mcrypt_create_iv') {
                    return false;
                } else {
                    return __GO_TO_ORIG__;
                }
            },
            Welcome::class
        );
~~~

#### Patch Other Functions

If you want to patch other functions, you can add them to [functions_to_patch](https://github.com/kenjis/monkey-patch/blob/a11e1f227234dadeae2460d29b9c8ca6e91c88de/src/bootstrap.php#L56-L59) in `MonkeyPatchManager::init()`.

But there are a few known limitations:

- Patched functions which have parameters called by reference don't work.
- You may see visibility errors if you pass non-public callbacks to patched functions. For example, you pass `[$this, 'method']` to `array_map()` and the `method()` method in the class is not public.

### Patch Methods in User-defined Classes

This patcher allows replacement of methods in user-defined classes.

~~~php
    public function test_index()
    {
        MonkeyPatch::patchMethod(
            Category_model::class,
            ['get_category_list' => [(object) ['name' => 'Nothing']]]
        );
        
        $output = $this->request('GET', 'welcome/index');
        
        $this->assertContains('Nothing', $output);
    }
~~~

`MonkeyPatch::patchMethod()` replaces `get_category_list()` method in `Category_model`, and it will return `[(object) ['name' => 'Nothing']]` in the test method.

### Patch Constants

This patcher allows replacement of constant value.

~~~php
    public function test_index()
    {
        MonkeyPatch::patchConstant('ENVIRONMENT', 'development', Welcome::class . '::index');
        
        $output = $this->request('GET', 'welcome/index');
        
        $this->assertContains('development', $output);
    }
~~~

``MonkeyPatch::patchConstant()` replaces the return value of the constant `ENVIRONMENT` in `Welcome::index` method.

There are a few known limitations:

- Cannot patch constants that are used as default values in function arguments.
- Cannot patch constants that are used as default values in constant declarations.
- Cannot patch constants that are used as default values in property declarations.
- Cannot patch constants that are used as default values in static variable declarations.

## Class Reference

See [ci-phpunit-test docs](https://github.com/kenjis/ci-phpunit-test/blob/3.x/docs/FunctionAndClassReference.md#class-monkeypatch).

## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE`](LICENSE).
