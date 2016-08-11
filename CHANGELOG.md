# Change Log

All notable changes to Pug will be documented in this file (beginning with v0.5 😅).

## [0.6.0] - 2015-08-10
### Added
- Namespaces
- Command support for namespaces (aka groups): enable, disable, remove, update
- Rename projects

### Fixed
- Always rebase, not just when changes are fetched

### Removed
- Subversion support

## [0.5.0] - 2016-08-06
### Added
- `pug upgrade`
- ./pug -> ./bin/pug symlink
- Self cleanup
- `pug.update.rebase true`

### Fixed
- Fix `pug update` to always return submodules to their original states

### Deprecated
- Subversion support
