# Summary

Provide Integration with the Zippy Library.

# Define custom binary path

Each binary utility comes with two binary path one for the inflator and the other for the deflator. By default if none is provided, zippy will look to find the executable by its name;

gnu-tar.inflator
gnu-tar.deflator
bsd-tar.inflator
bsd-tar.deflator
zip.inflator
zip.deflator


## TODO before contrib module

* Add all adapters/strategies as services
* Replace Zippy::Load into the factory using DI
* Implement plugin system for current and new adapters/strategies
