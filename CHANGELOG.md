## v0.4.0 (April 2, 2022)

### 💥 Breaking changes

* Bump league/flysystem to version `^3.0` ([99a30eb](https://github.com/webalternatif/flysystem-dsn/commit/99a30ebea11f9780ff2238fee8782703d1311d4d))

## v0.3.2 (December 30, 2021)

### ✨ New features

* Add support of PHP 8.1 ([c844793](https://github.com/webalternatif/flysystem-dsn/commit/c84479340ca13477c0eab064c33bae01b666ad34))

## v0.3.1 (November 7, 2021)

### ✨ New features

* Add `FtpAdapterFactory` ([4144d41](https://github.com/webalternatif/flysystem-dsn/commit/4144d4121428810b86c55db8d192b01b6b934374))
* Add `SftpAdapterFactory` ([a1605af](https://github.com/webalternatif/flysystem-dsn/commit/a1605af0668737bfa23a16f276409bd525bc5fdc))

## v0.3.0 (October 8, 2021)

### 💥 Breaking changes

* Add `InvalidDsnParameterException`, possibly thrown by `FlysystemAdapterFactoryInterface::createAdapter()` ([3ddf198](https://github.com/webalternatif/flysystem-dsn/commit/3ddf19826f08ec568d1e0cdb0b2ef0d0b952357a))

### ✨ New features

* Add `InMemoryAdapterFactory` ([eb9aacc](https://github.com/webalternatif/flysystem-dsn/commit/eb9aacca37282c4928e471c078df48d39f61275c))
* Add `LocalAdapterFactory` ([a8092e2](https://github.com/webalternatif/flysystem-dsn/commit/a8092e23200f0fb31d74b787eaecb108bbc16454))

## v0.2.0 (September 14, 2021)

### 💥 Breaking changes

* Add `UnableToCreateAdapterException`, possibly thrown by `FlysystemAdapterFactoryInterface::createAdapter()` ([3731ea6](https://github.com/webalternatif/flysystem-dsn/commit/3731ea622a5e45169c2ff02c9f5a19e20f5c5126))

### ✨ New features

* Add `FailoverAdapterFactory` ([ce845a4](https://github.com/webalternatif/flysystem-dsn/commit/ce845a4ebb8e7ec58740cf37a0a5a14ffd349401))

### 🐛 Bug fixes

* Do not throw `FunctionsNotAllowedException` in `supports` method of `AwsS3AdapterFactory` and `OpenStackSwiftAdapterFactory` ([7840eb4](https://github.com/webalternatif/flysystem-dsn/commit/7840eb4263dc73016bc2155d48e4cb77d487cfae))
* Allow DSN functions in `FlysystemAdapterFactory::createAdapter()` ([662b010](https://github.com/webalternatif/flysystem-dsn/commit/662b010677af5949860d159c74c146ce41d79ea6))

## v0.1.0 (August 30, 2021)

First version. ([833160d](https://github.com/webalternatif/flysystem-dsn/commit/833160dc94e29eccd6139894c7f6652cf2a8693c))
