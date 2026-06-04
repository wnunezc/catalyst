# Catalyst v0.1.0-rc.5 Upgrade Notes

## Scope

`v0.1.0-rc.5` supersedes `v0.1.0-rc.4` and closes GitHub issues #7 and #8:

- stops tracking active development config files for `app`, `db` and `session`;
- tracks safe first-run templates as `app.example.json`, `db.example.json` and `session.example.json`;
- adds `config:sync` to create missing active files and merge new template keys without replacing local values;
- adds `config:contract-smoke` and wires it into `quality:check`;
- relaxes `admin-navigation:smoke` from exact group equality to ordered canonical preservation, allowing valid derived-app sidebar entries;
- keeps RC4 admin sidebar taxonomy and existing framework surfaces unchanged.

## Local Upgrade

From a derived project that tracks Catalyst as `upstream`:

```powershell
git status --short --branch
git fetch upstream --tags
git merge v0.1.0-rc.5
composer install
php public/cli.php config:sync
php public/cli.php config:contract-smoke --json
php public/cli.php migrate:status
php public/cli.php admin-navigation:smoke --json
php public/cli.php quality:check
```

If the merge reports upstream deletions for:

```text
boot-core/config/development/app.json
boot-core/config/development/db.json
boot-core/config/development/session.json
```

accept the index removal but keep the local files on disk. They are now ignored
by Git and owned by the derived project. `config:sync` will create any missing
file from the matching `.example.json` template and add only missing keys.

## Verification

Run:

```powershell
git ls-files --error-unmatch boot-core/config/development/app.json
git check-ignore boot-core/config/development/app.json
php public/cli.php config:sync --json
php public/cli.php config:contract-smoke --json
php public/cli.php distribution:smoke --json
php public/cli.php quality:check
```

The first command should fail because active local config is no longer tracked.
The second command should pass because the file is ignored.

For a derived project such as RTM-Hub, verify after the merge that local values
such as app URL, database name, database user and session name remain unchanged.
Derived navigation entries such as `/rtm/profile` and `/rtm/radio` may appear in
canonical groups as long as Catalyst canonical hrefs and labels remain present.
