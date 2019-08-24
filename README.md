# Rector Prefixer

[![Build Status](https://img.shields.io/travis/rectorphp/prefixer/master.svg?style=flat-square)](https://travis-ci.org/rectorphp/prefixer)

This tool builds prefixed Rector.

### When do you need Prefixed Rector?

- When you run `composer require rector/rector` and fail on PHP code API conflicts - e.g. you use Symfony 2.8, but Rector requires 3.4. 
- If you need PHP 7.0 and bellow, use [Rector in the Docker](https://github.com/rectorphp/rector#run-rector-in-docker).

## Compile Prefixed `rector.phar`

```
composer install
bin/console compile

# prefixing and compiling to rector.phar, might take 2-3 mins

# final file
tmp/rector.phar
```
