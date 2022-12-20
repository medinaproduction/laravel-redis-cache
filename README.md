# Package Stub
This is a stub/boilerplate for creating packages.

### How to use
To make a new package from this, follow these steps:
1. Fork the stub repository into a new package repository
2. In your projects `composer.json` file, make sure you have the following:

```
"require": {
    ...
    "mnsami/composer-custom-directory-installer": "^2.0",
}
```
```
"extra": {
    ...
    "installer-paths": {
        ...
        "./support/new-package-name": ["Rivercode/my-new-package"],
    }
},
```
```
"config": {
    ...
    "allow-plugins": {
        "mnsami/composer-custom-directory-installer": true
    }
},
```
3. Also add the following to the `repositories` section:
```
"repositories": [
    ...
    {
        "type": "vcs",
        "url": "git@github.com:Rivercode/my-new-package.git"
    }
]
```
4. After the fork has been made, run the following command:
```
composer require Rivercode/my-new-package --prefer-source`
```
5. Move into the new package folder
6. Rename the stub package filenames and namespaces as required
7. Commit and add tag `0.0.1`
8. Done!

### Get tests to work
_Note: This guide is for testing in a standalone folder. If you want to run tests within the `support` folder, the process may be a bit different._
1. Rename `phpunit.xml.dist` to `phpunit.xml`
2. Naviage to package in termianl `cd my-new-package`
3. Make sure you have run `composer update` in your package folder
4. Run tests `./vendor/bin/phpunit`