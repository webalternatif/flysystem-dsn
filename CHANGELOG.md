## v0.3.2 (December 30, 2021)

### ✨ New features

* Add support of PHP 8.1

## v0.3.1 (November 7, 2021)

### ✨ New features

* Add `FtpAdapterFactory`
* Add `SftpAdapterFactory`

## v0.3.0 (October 8, 2021)

### 💥 Breaking changes

* Add `InvalidDsnParameterException`, possibly thrown by `FlysystemAdapterFactoryInterface::createAdapter()`

### ✨ New features

* Add `InMemoryAdapterFactory`
* Add `LocalAdapterFactory`

## v0.2.0 (September 14, 2021)

### 💥 Breaking changes

* Add `UnableToCreateAdapterException`, possibly thrown by `FlysystemAdapterFactoryInterface::createAdapter()`

### ✨ New features

* Add `FailoverAdapterFactory`

### 🐛 Bug fixes

* Do not throw `FunctionsNotAllowedException` in `supports` method of `AwsS3AdapterFactory` and `OpenStackSwiftAdapterFactory`
* Allow DSN functions in `FlysystemAdapterFactory::createAdapter()`

## v0.1.0 (August 30, 2021)

First version.
