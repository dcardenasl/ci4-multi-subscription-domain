# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **`SyncPermissions` command** — auto-clears local and admin caches after sync in development environments (DX improvement).
- **`SyncPermissions` command** — auto-mints a temporary superadmin token from the Hub database in development, so `--admin-token` is no longer required for local runs when `hub.adminToken` is not set.
