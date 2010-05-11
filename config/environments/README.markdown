Environments
============

Zing! supports an arbitrary number of configuration profiles, or "environments".

To create a new environment, simply create a new subdirectory in here.

Every environment must include a master configuration file, `main.php`.

A standard environment script writes to the provided `$ENV` array. Its values may then either be merged with the master `$_ZING` configuration variable (via `zing_load_environment()`), or returned to the caller without merging (via `zing_export_environment()`). You can query for all available environments using `zing_environments()`.