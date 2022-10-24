# Developer Tools component

This component contains additional developer tools for O3-Shop.

## Installation

Run the following command to install the component:

```bash
composer require o3-shop/developer-tools
```

## Usage

### Resetting project configuration
To reset project configuration to its initial state execute:

```bash
bin/oe-console oe:module:reset-configurations 
```

## How to install component for development?

Checkout component besides O3-Shop `source` directory:

```bash
git clone https://github.com/o3-shop/developer-tools.git
```

Run composer install command:

```bash
cd developer-tools
composer install
```

Add dependency to O3-Shop `composer.json` file:

```bash
composer config repositories.o3-shop/developer-tools path developer-tools
composer require --dev o3-shop/developer-tools:*
```

## How to run tests?

To run tests for the component please define O3-Shop bootstrap file:

```bash
vendor/bin/phpunit --bootstrap=../source/bootstrap.php tests/
```

## License

See [LICENSE](LICENSE) file for license details.
