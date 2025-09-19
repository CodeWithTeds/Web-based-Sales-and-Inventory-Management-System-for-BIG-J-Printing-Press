# Git Naming Convention

This document outlines the Git naming conventions for the Inventory Management System (IMS) project. Following these conventions ensures consistency, improves collaboration, and makes the project history more readable and maintainable.

## Branch Naming Convention

Branches should follow this format:

```
<type>/<issue-number>-<short-description>
```

Where:
- `<type>` is one of:
  - `feature`: For new features or enhancements
  - `bugfix`: For bug fixes
  - `hotfix`: For critical fixes that need immediate deployment
  - `refactor`: For code refactoring without changing functionality
  - `docs`: For documentation updates only
  - `test`: For adding or modifying tests
  - `chore`: For maintenance tasks, dependency updates, etc.
- `<issue-number>` is the ticket/issue number (if applicable)
- `<short-description>` is a brief, hyphenated description of the change

Examples:
- `feature/123-add-material-request-form`
- `bugfix/456-fix-validation-error`
- `refactor/789-improve-controller-structure`
- `docs/234-update-api-documentation`

## Commit Message Convention

Commit messages should follow this format:

```
<type>(<scope>): <subject>

<body>

<footer>
```

Where:

### Type

The `<type>` must be one of:

- `feat`: A new feature
- `fix`: A bug fix
- `refactor`: Code change that neither fixes a bug nor adds a feature
- `style`: Changes that do not affect the meaning of the code (formatting, etc.)
- `docs`: Documentation only changes
- `test`: Adding or correcting tests
- `chore`: Changes to the build process, auxiliary tools, libraries, etc.
- `perf`: Performance improvements

### Scope

The `<scope>` is optional and represents the module, component, or area of the codebase that is affected.

Examples: `controller`, `model`, `view`, `validation`, `auth`, etc.

### Subject

The `<subject>` should:
- Be concise (50 characters or less)
- Begin with a lowercase letter
- Not end with a period
- Use imperative mood ("add" not "added" or "adds")

### Body

The `<body>` is optional and should provide context and explain what and why (not how).

### Footer

The `<footer>` is optional and should reference issues, PRs, breaking changes, etc.

## Examples

### Feature Addition

```
feat(material): add request form validation

Implement form validation for material request form to ensure data integrity.
Validation includes required fields, numeric constraints, and date validation.

Resolves #123
```

### Bug Fix

```
fix(controller): correct method compatibility issue

Fix method signature incompatibility between MaterialController and BaseController.
Update parameter types to ensure proper inheritance.

Fixes #456
```

### Refactoring

```
refactor(api): improve response handling

Refactor API response handling to support both JSON and view responses.
This change improves code reusability and follows DRY principles.

No functional changes.
```

## Pull Request Naming

Pull request titles should follow the same format as commit messages:

```
<type>(<scope>): <subject>
```

Example: `feat(material): add request form validation`

## Merging Strategy

- Use squash merging for feature branches to maintain a clean history
- Preserve merge commits for major features or releases
- Delete branches after merging

## Additional Guidelines

1. Keep commits atomic and focused on a single change
2. Write descriptive commit messages that explain the "why" not just the "what"
3. Reference issue numbers in commit messages and pull requests
4. Rebase feature branches on main before creating pull requests
5. Use git hooks to enforce commit message format when possible

Following these conventions will help maintain a clean, readable, and useful Git history for the project.