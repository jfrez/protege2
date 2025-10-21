# Project Contribution Guidelines

Welcome to the Protege2 codebase! This document describes the expectations for anyone modifying
files within this repository. Please read it carefully before making changes.

## Coding Conventions
- Follow PSR-12 for PHP code (indent with 4 spaces, brace on next line, spaces around operators).
- Keep Python files formatted with `black` (line length 88) and linted with `ruff` using the
  repository's existing configuration.
- Use descriptive, snake_case names for variables and functions in Python; use camelCase for PHP
  variables and methods, and StudlyCaps for PHP classes.
- Avoid duplicating logic. Prefer extracting reusable helpers into the `utils/` directory.
- Include docblocks or docstrings for complex functions explaining inputs, outputs, and side effects.
- Do not commit debugging code (e.g., `var_dump`, `print`, or commented-out diagnostics).

## Testing Expectations
- Run the project's test suite before submitting changes:
  - PHP: execute `./run_tests.sh` to run the available checks.
  - Python: run `python -m unittest discover` or the appropriate module-specific tests affected by
    your changes.
- If you modify database schema files, document any required migrations and update sample data when
  applicable.
- Add or update tests alongside feature changes or bug fixes whenever feasible.
- Document any manual verification steps in your commit message or PR description when automated
  tests are not available.

## Pull Request Message Format
Every pull request should include:
1. **Summary** – bullet list describing each significant change.
2. **Testing** – bullet list of commands executed, each prefixed with an emoji indicating the
   result (`✅` pass, `⚠️` warnings or skipped, `❌` failures) and the command that was run.
3. **Screenshots** – when UI changes are made, attach browser screenshots (using the provided
   automation tooling) and reference them in the PR body.
4. **Additional Notes** – optional section for deployment steps, migrations, or follow-up work.

## Tooling Guidelines
- Use the existing `run_tests.sh` script for comprehensive checks before committing.
- Prefer `docker-compose up` for local environment setup; update the `docker-compose.yml` if service
  requirements change.
- When adding dependencies, ensure they are recorded in the appropriate configuration files and the
  container images are kept in sync.
- Keep large assets out of the repository; use links or document how to regenerate them.
- Coordinate significant architectural changes with the maintainers via an issue before starting
  work.

Thank you for contributing and maintaining consistency across the project!
