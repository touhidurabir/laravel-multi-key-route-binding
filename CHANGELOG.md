# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]
- Laravel 11, 12 and 13 support added.
- PHPUnit 10/11/12 support for tests.
- Tests no longer rely on the `bindings` middleware alias, which does not exist on Laravel 11+.
- Code style formatted to PSR-12 with Laravel Pint (`composer format` / `composer lint`).
- Replaced Travis CI with GitHub Actions (PHP 8.1 × Laravel 8/9/10 and PHP 8.2–8.4 × Laravel 11/12/13, plus a code style check).
- Fixed the extra binding keys being ignored on `->withTrashed()` routes and on scoped child bindings
  (`/users/{user}/posts/{post}` with `scopeBindings()`), which returned 404 for e.g. an email or username. The trait
  now overrides `resolveRouteBindingQuery()`, which Laravel uses for every binding path.
- The route key and extra binding key conditions are now grouped in a nested `where (... or ...)`, so they can not
  escape other constraints on the query, such as the parent constraint of a scoped child binding.
- Added the opt-in `$useModelRouteKeyName` model property. When it is `true`, `getRouteKeyName()` returns the model's
  own route key name (its primary key by default) instead of the hard-coded `'id'`. The default is unchanged.
- Fixed the binding key column check (`Schema::hasColumn`) using the default database connection instead of the
  model's connection, which skipped the extra keys for models on another connection.
- Fixed the `MultiKyeRouteBinding` facade: its `multi-key-route-binding` container key was never bound, so any facade
  call failed. It now resolves to `Touhidurabir\MultiKyeRouteBinding\RouteBindingResolver`, whose
  `resolve($model, $value, $field = null)` finds a model the same way route model binding does.


## [1.0.0] - 2021-08-16
- Initial release

## [1.0.1] - 2021-10-29
- Bug fix.