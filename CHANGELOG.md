## v0.2.0 (unreleased)

### 💥 Breaking changes

  * Add `UnableToCreateAdapterException`, possibly thrown by `FlysystemAdapterFactoryInterface::createAdapter()`

### ✨ New features

  * Add `FailoverAdapterFactory`

### 🐛 Bug fixes

  * Do not throw `FunctionsNotAllowedException` in `supports` method of `AwsS3AdapterFactory` and `OpenStackSwiftAdapterFactory` 

## v0.1.0 (August 30, 2021)

First version.
