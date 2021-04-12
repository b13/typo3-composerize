# TYPO3 Composerize

## What does it do?

`typo3-composerize check` - will check extensions for composer compatability. A extension is considered incompatible if either `composer.json` 
does not exist or the `composer.json` does not contain an `extension-key` property in `extra -> typo3/cms` section

`typo3-composerize create` - if the `composer.json` does not exist it will send a request containing the contents of `ext_emconf.php` as a JSON payload to TER (POST https://extensions.typo3.org/composerize/<ext key>) and returns a `composer.json` which will be stored in the extension folder. In case the `extension-key` is missing it will automatically set according to the extensions folder name. 

## Usage

Install: `composer require b13/typo3-composerize`

Check if given extensions are compatible:

* `./vendor/bin/typo3-composerize check -d <doc root> "ext_key1,ext_key2"`

Update given extensions:

 * `./vendor/bin/typo3-composerize create -d <doc root> "ext_key1,ext_key2"`

## Tests & Linting

Install composer including `require-dev`: `composer install --dev`

Run ...

* PHPUnit - `./vendor/bin/phpunit`
* PHPStan - `./vendor/bin/phpstan analyse --no-progress Classes/ Tests/`
* PHP CS Fixer - `./vendor/bin/php-cs-fixer fix -vvv --dry-run --diff`
