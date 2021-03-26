# TYPO3 Composerize

Validate and prepare extensions to be handled by composer

 * `./vendor/bin/typo3-composerize check -d public "ext_key1,ext_key2"`
   * `-d` - document root
 * `./vendor/bin/typo3-composerize create -d public "ext_key1,ext_key2"` 
     * `-d` - document root

## Tests & Linting

Install composer including `require-dev`: `composer install --dev`

Run ...

* PHPUnit - `./vendor/bin/phpunit`
* PHPStan - `./vendor/bin/phpstan analyse --no-progress Classes/ Tests/`
* PHP CS Fixer - `./vendor/bin/php-cs-fixer fix -vvv --dry-run --diff`
