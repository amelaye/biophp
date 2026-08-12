# CLAUDE.md

## Project overview

BioPHP is a PHP 8.2+ library and Symfony 7 bundle for bioinformatics. It exposes
biological reference data through API adapters, provides sequence and database
services, and contains Doctrine entities for parsed biological records.

The migration off PHP 7.2/Symfony 4 is complete: `composer.json` requires
`php: ^8.2`, `symfony/*: ^7.4`, `doctrine/orm: ^3.6`, and `phpunit/phpunit:
^11.5`. CI (`.github/workflows/tests.yml`) runs the suite on PHP 8.2, 8.3, 8.4,
and 8.5 for both the `master` and `develop` branches, with coverage collected
only on the 8.2 job. `master` and `develop` currently track the same history;
treat these conventions as applying to both.

## Repository map

- `Api/`: HTTP API clients, adapter interfaces, and DTOs. API Platform/Hydra
  responses are read from the `hydra:member` field.
- `Domain/Sequence/`: sequence entities, interfaces, traits, the
  `SequenceBuilder` facade, and sequence-related services.
- `Domain/Database/`: database entities, factories, and GenBank/Swiss-Prot
  parsers.
- `Domain/Tools/`: reusable genetics, mathematics, and oligonucleotide helpers.
- `DependencyInjection/`: Symfony bundle configuration and service loading.
- `*/Resources/config/services.xml`: Symfony service definitions and interface
  aliases.
- `Tests/`: PHPUnit tests mirroring the production namespaces. Test fixtures are
  stored in nearby `samples/` directories.
- `data/`: local GenBank, Swiss-Prot, FASTA, and alignment samples used by tests.
- `Legacy/`: original BioPHP source kept for reference and licence continuity.
  Do not modify or modernize it unless the task explicitly targets legacy code.
- `docs/`: dated progress notes from the PHP 8 migration (not authoritative
  reference material; read `git log` and the code itself for current state).

The root namespace is `Amelaye\BioPHP\`; tests use `Tests\`.

## Setup and verification

Install dependencies:

```bash
composer install
```

Run the complete test suite:

```bash
vendor/bin/phpunit -c phpunit.xml
```

Run one test file or one test method while iterating:

```bash
vendor/bin/phpunit -c phpunit.xml Tests/Domain/Sequence/Service/SequenceManagerTest.php
vendor/bin/phpunit -c phpunit.xml --filter testComplement
```

Check the syntax of a changed PHP file:

```bash
php -l path/to/File.php
```

The CI coverage command is:

```bash
mkdir -p build/logs
vendor/bin/phpunit --coverage-clover build/logs/clover.xml -c phpunit.xml
```

There is no configured formatter or static analyser. Do not introduce one as
part of an unrelated change. This is a library, and `composer.lock` is ignored;
do not add it.

## Implementation conventions

- The minimum supported version is PHP 8.2 (CI also runs 8.3, 8.4, 8.5), so
  PHP 8.2+ syntax is allowed. That said, outside of Doctrine attribute mapping
  the codebase has not adopted newer constructs (constructor property
  promotion, `readonly` properties, enums, `match`, union/intersection types).
  Follow the style of the file being edited rather than introducing these
  unless the task calls for it: four spaces, one class per file, explicit
  visibility, PHPDoc `@var`/`@param`/`@return`, and scalar parameter/return
  types where practical.
- Keep the existing public API stable unless a breaking change is explicitly
  requested. This includes method signatures, DTO accessors, adapter interfaces,
  Symfony service IDs, and interface aliases.
- Keep domain calculations in managers/services. `SequenceBuilder` is the
  stateful facade that obtains defaults from a `Sequence` entity and delegates
  calculations to `SequenceManager`.
- API classes extend `Bioapi`, receive Guzzle and JMS Serializer dependencies,
  and convert remote payloads into DTOs. Do not make live HTTP calls in tests;
  mock adapters or clients and use `Tests/**/samples` fixtures.
- Doctrine mapping uses PHP 8 attributes (e.g. `#[ORM\Entity]`,
  `#[ORM\Column(...)]`), not docblock annotations. When changing an entity,
  keep its attributes, PHP types, accessors, and related parser behavior
  consistent.
- Service wiring is XML. When adding or changing a constructor dependency,
  update the appropriate `Resources/config/services.xml` definition. When a
  service implements a public domain interface, preserve or add its interface
  alias.
- Parsing logic depends on exact source-file formatting and the fixtures in
  `data/`. Preserve whitespace, indexing, case, and biological notation unless
  the intended behavior explicitly changes them.
- Avoid broad cleanup in bug fixes. Some historical naming and formatting is
  inconsistent; changing it can break consumers of this alpha library.

## Testing expectations

- Add or update a test for every behavior change and regression fix.
- Place tests under the production-equivalent path in `Tests/` and name them
  `*Test.php`.
- Reuse the existing sample arrays and files where appropriate. Add a focused
  fixture only when an existing one cannot express the case.
- Test public behavior and edge cases, especially empty or invalid sequences,
  DNA versus RNA behavior, ambiguous nucleotide symbols, zero-based positions,
  and parser whitespace.
- Run the narrowest relevant test during development, then the complete suite
  before finishing. If dependencies or the required PHP version are unavailable,
  report exactly which checks could not be run.

## Change checklist

Before completing a change:

1. Confirm the change remains compatible with PHP 8.2 (the minimum supported
   version) and current dependencies.
2. Check whether an interface, service XML file, DTO, entity mapping, fixture, or
   parser must change with the implementation.
3. Run `php -l` on changed PHP files.
4. Run focused PHPUnit tests and then the full suite.
5. Review the diff for accidental edits to `Legacy/`, generated `build/` output,
   local IDE files, or `composer.lock`.
