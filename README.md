# Cleaner Result Printer

## Install

```bash
composer require --dev leanbooktools/cleaner-result-printer
```

## Usage

Register extension in your `phpunit.xml` file:

```xml
<extensions>
    <bootstrap class="LeanBookTools\CleanerResultPrinterExtension" />
</extensions>
```

## Adding test cases to the end-to-end tests

- Add a test that demonstrates your case, e.g. a risky test in `fixture/DemoProject/tests`.
- Add a file with the expected output (see the folder for examples).
- Run the tests: `vendor/bin/phpunit`.
- To find out what normal PHPUnit output looks like, run `bin/phpunit-reference-tests`. This runs the same tests, but uses the built-in result printer.
