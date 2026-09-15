# BioPHP Development Skill

## Project

**Repository:** `amelaye/biophp`

BioPHP is a PHP library for biological and bioinformatics operations.

The project originated from the historical BioPHP project and contains both legacy code and a newer object-oriented architecture.

The goal is **not to rewrite everything from scratch**, but to progressively modernize the project while preserving its biological features and compatibility whenever reasonable.

The codebase currently contains several major areas:

```text
Api/
DependencyInjection/
Domain/
Legacy/
Tests/
data/
AmelayeBioPHPBundle.php
composer.json
phpunit.xml
```

The `Domain/` directory is the primary modern implementation.

The `Legacy/` directory contains historical BioPHP code and must be treated as legacy compatibility code.

The `Api/` layer communicates with the BioPHP REST API for biological reference data.

---

# Main Development Goal

When working on this repository, prioritize:

1. Modern PHP architecture.
2. Clear separation between biological domain logic and infrastructure.
3. Testability.
4. Backward compatibility where practical.
5. Progressive removal or isolation of legacy architecture.
6. Reduction of obsolete Symfony/PHP dependencies.
7. Correctness of biological algorithms above clever implementation.

Do **not** perform large rewrites unless explicitly requested.

Prefer small, understandable, testable refactors.

---

# Architecture

## Domain

`Domain/` contains the core biological logic.

New biological functionality should normally live here.

Typical concepts include:

* sequences
* nucleotides
* amino acids
* proteins
* DNA
* RNA
* genetic codes
* restriction sites
* sequence analysis

Domain code should remain as independent as possible from Symfony and external APIs.

A biological algorithm should ideally be usable without booting a Symfony application.

---

## API

`Api/` contains clients used to retrieve biological reference data from the BioPHP API.

Examples include API access for:

* amino acids
* nucleotides
* biological elements

Do not put biological algorithms directly inside API clients.

API clients retrieve or deserialize data.

Domain services manipulate biological data.

Keep these responsibilities separate.

---

## Dependency Injection

`DependencyInjection/` contains Symfony integration.

Symfony-specific configuration belongs here rather than inside domain services.

When modernizing code, avoid introducing new Symfony dependencies into `Domain/`.

The long-term direction should make BioPHP increasingly usable as a normal Composer library independently of Symfony.

---

## Legacy

`Legacy/` contains historical BioPHP implementations.

Treat this directory carefully.

### Rules

Do not:

* refactor legacy files just for style
* rename legacy public functions unnecessarily
* silently change legacy behavior
* move modern code back into the legacy architecture

When replacing legacy functionality:

1. understand the original behavior
2. write tests describing it
3. implement the equivalent feature in the modern domain
4. compare outputs
5. preserve compatibility when useful

Legacy code is a reference implementation, not the architectural model for new code.

---

# PHP Modernization

The project historically supported PHP 7.x and Symfony 4.

When modifying existing code, progressively adopt modern PHP practices when they are compatible with the current modernization target.

Prefer:

```php
declare(strict_types=1);
```

Use:

* typed parameters
* typed return values
* typed properties
* constructor injection
* small immutable value objects when appropriate
* exceptions instead of magic error values
* interfaces when they represent meaningful boundaries

Avoid:

* global state
* static service locators
* unnecessary inheritance
* giant manager classes
* hidden side effects
* dynamically created properties
* obsolete PHP APIs

Do not mechanically modernize hundreds of files in one change.

Modernize code touched by the current task.

---

# Biological Correctness

BioPHP manipulates scientific data.

Correctness is more important than shortening code.

Never assume biological rules when uncertain.

For algorithms involving DNA, RNA or proteins, explicitly consider:

* DNA vs RNA alphabets
* uppercase/lowercase input
* invalid symbols
* ambiguous nucleotide codes
* empty sequences
* sequence orientation
* 5' → 3' direction
* complementary strands
* reverse complements
* codon boundaries
* stop codons
* unknown amino acids

Do not silently normalize scientifically meaningful input unless the existing API explicitly does so.

---

# Sequence Algorithms

Sequence operations should preferably be deterministic and side-effect free.

Example shape:

```php
$result = $sequenceService->reverseComplement($sequence);
```

Prefer returning values rather than mutating shared objects.

For every new sequence algorithm, create tests covering:

```text
normal input
empty input
minimal input
invalid input
boundary cases
known biological example
```

When fixing a biological algorithm, add a regression test demonstrating the bug before changing the implementation.

---

# External Biological Data

BioPHP can obtain reference information through its REST API.

Do not assume the API is always available.

Whenever possible, separate:

```text
data retrieval
      ↓
data representation
      ↓
biological computation
```

A domain algorithm should not unexpectedly perform HTTP requests.

HTTP access must remain explicit through API/repository abstractions.

---

# HTTP/API Code

When modifying API clients:

* use dependency injection
* never instantiate the HTTP client inside domain methods
* handle HTTP failures explicitly
* validate deserialized responses
* avoid hard-coded production URLs inside domain logic
* make clients mockable in tests

Never commit:

* API keys
* tokens
* passwords
* private endpoints
* credentials

---

# Symfony

BioPHP historically works as a Symfony bundle.

Do not assume every BioPHP user runs Symfony.

When implementing new functionality, prefer:

```text
PHP library
    ↓
optional Symfony integration
```

rather than:

```text
Symfony service
    ↓
business logic
```

Symfony should be an adapter around BioPHP, not the foundation of the biological domain.

---

# Tests

Tests live under:

```text
Tests/
```

Every bug fix should ideally include a regression test.

Every new biological feature must include tests.

Before considering a task complete, run the project's available test suite.

Typically inspect:

```bash
composer install
vendor/bin/phpunit
```

before introducing additional tooling.

Never claim tests pass unless they were actually executed.

If tests cannot run because of obsolete dependencies or PHP incompatibility, explain the exact blocker.

Do not modify tests merely to make a failing implementation appear correct.

---

# Composer

`composer.json` is part of the public contract of the library.

Changes to dependencies must be deliberate.

Before adding a package, ask:

1. Can this reasonably be implemented without another dependency?
2. Is the dependency maintained?
3. Does it support the target PHP version?
4. Does it introduce Symfony coupling?
5. Is it necessary at runtime or only during development?

Prefer maintained dependencies.

Remove obsolete dependencies only after verifying that no code still depends on them.

After Composer changes, verify dependency resolution and run the tests.

---

# Security

Treat all external input as untrusted.

Especially validate:

* sequence strings
* API responses
* filenames
* URLs
* serialized data
* user-provided biological data

Avoid unsafe deserialization.

Do not use:

```php
eval()
```

Do not execute shell commands constructed from untrusted values.

Do not disable TLS verification to solve HTTP problems.

When encountering an obsolete dependency with a known vulnerability, report it and propose a migration rather than hiding the warning.

---

# Coding Style

Prefer readable PHP over compressed PHP.

Good:

```php
public function complement(string $sequence): string
{
    // clear implementation
}
```

Avoid unnecessary abstractions such as:

```text
AbstractSequenceManagerFactoryProviderInterface
```

unless the architecture genuinely requires them.

Names should reflect biological concepts.

Prefer:

```text
Sequence
Nucleotide
AminoAcid
Codon
GeneticCode
RestrictionSite
```

over generic names such as:

```text
Data
Helper
Processor
Utils
Thing
```

---

# Refactoring Strategy

For significant modernization work, use this sequence:

```text
inspect
↓
understand
↓
test existing behavior
↓
isolate responsibility
↓
refactor
↓
run tests
↓
review compatibility
```

Never start with a rewrite simply because the existing implementation is old.

Old code may contain biological behavior that is not obvious from its structure.

---

# Working With Legacy Features

When asked to modernize an old BioPHP function:

## Step 1 — Locate implementations

Search both:

```text
Legacy/
Domain/
Tests/
```

Determine whether a modern equivalent already exists.

## Step 2 — Understand behavior

Identify:

* inputs
* outputs
* edge cases
* biological assumptions
* exceptions/errors
* dependencies

## Step 3 — Find tests

Existing tests are part of the specification.

Do not assume the implementation itself is correct when tests and biological knowledge disagree.

## Step 4 — Implement

Prefer implementation inside `Domain/`.

## Step 5 — Preserve compatibility

If an old public API remains useful, consider implementing it as an adapter around the modern domain rather than duplicating the algorithm.

---

# Relationship With Other Amelaye BioPHP Projects

BioPHP is part of a larger ecosystem.

Related components may include:

```text
BioPHP
BioPHP API
BioTools
Demo application
```

Keep responsibilities clear.

### BioPHP

Biological models, algorithms and reusable PHP services.

### API

Provides biological reference data over HTTP.

### BioTools

Higher-level biological tools and applications built using BioPHP.

### Demo

Demonstrates how the ecosystem can be used.

Do not move application-specific BioTools behavior into the core BioPHP library unless it represents genuinely reusable biological functionality.

---

# Claude Workflow

When receiving a development request for this repository:

## 1. Explore first

Inspect relevant:

```text
composer.json
Domain/
Api/
Legacy/
Tests/
```

Do not propose architecture based only on filenames.

Read the implementation involved.

## 2. Search before creating

Before adding a class or function, search for equivalent functionality.

BioPHP contains legacy and modern implementations, so duplication is particularly easy.

## 3. Explain significant discoveries

If you discover:

* duplicate implementations
* dead code
* incompatible PHP code
* obsolete dependencies
* incorrect biological logic
* missing tests
* security problems

mention them before performing a broad unrelated refactor.

## 4. Make focused changes

Do not modify unrelated files.

Avoid formatting entire directories as a side effect of a small feature.

## 5. Verify

Run the relevant tests.

For broad changes, run the complete suite when possible.

## 6. Report

At the end of the task summarize:

```text
What changed
Tests executed
Compatibility impact
Remaining technical debt
```

---

# Git Rules

Keep commits conceptually focused.

Do not mix:

```text
PHP modernization
biological feature
formatting
dependency upgrades
documentation rewrite
```

into one giant change unless explicitly requested.

Never:

* force push
* rewrite repository history
* delete branches
* remove tags

unless explicitly instructed.

Do not commit generated dependency directories such as:

```text
vendor/
```

---

# Documentation

Public APIs should be documented.

Documentation should explain biological meaning where useful, not merely repeat PHP types.

Bad:

```php
/**
 * Gets the sequence.
 */
public function getSequence(): string
```

Useful:

```php
/**
 * Returns the nucleotide sequence in 5' → 3' orientation.
 */
public function getSequence(): string
```

Examples should use biologically valid data.

---

# Compatibility Philosophy

BioPHP is an old project undergoing modernization.

Therefore:

> Modernize aggressively internally, but break public behavior deliberately.

Before breaking a public class, method or namespace, identify:

* why the break is necessary
* what replaces it
* whether an adapter is possible
* whether documentation must change

Do not preserve bad architecture forever solely for backward compatibility, but do not create accidental breaking changes.

---

# Definition of Done

A BioPHP task is complete when:

* the requested behavior is implemented
* the architecture remains understandable
* biological behavior is correct
* relevant tests exist
* tests pass when the environment permits them to run
* no credentials or secrets were introduced
* no unrelated files were modified
* public compatibility impact is known
* documentation is updated when public behavior changed

---

# Guiding Principle

BioPHP should evolve from a historical Symfony-era biology project into a clean, maintainable, framework-independent PHP bioinformatics library.

Preserve what is scientifically useful.

Modernize what is technically obsolete.

Do not rewrite working biological logic without first understanding why it works.
