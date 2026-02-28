# Changelog

## 3.1.0 - 2026-02-28

### Changed

- Requires PHP `8.4`
- Requires `innmind/immutable:~6.0`

## 3.0.0 - 2025-08-23

### Changed

- Unused services are freed from memory

### Removed

- Using a `string` as a service name

### Fixed

- Service names in exception messages that used object hashes

## 2.1.0 - 2024-03-24

### Added

- Ability to use enums to reference services and specify the returned object type

### Removed

- Support for PHP `8.1`

### Deprecated

- Using `string`s as a service name
