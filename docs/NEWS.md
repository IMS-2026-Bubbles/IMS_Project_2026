# NEWS — Experiment_pages branch

## 2026-09-29 — Schema-name migration verified in code, link and documentation fixes

### Schema migration verified against the code
A full audit confirmed the PHP code now runs against the new schema: the
queries use the renamed tables (`experiments`, `profiles`, `companies`,
`labs`, the `*_members` junctions, `experiment_tags`), renamed columns
(`experiment_id`, `profile_id`, `*_text`, `*_is_done`, `*_updated_at`),
lowercase section values (`plan` / `log` / `result` in the whitelists and
links), and `$_SESSION['profile_id']`. The `Scriba_Member` table is gone from
the code — the Scriba-admin check reads `profiles.is_scriba_admin` instead.
The manual `SET <section>_Updated = NOW()` statement was removed from
`save_experiment_section.php` (the new schema's `ON UPDATE CURRENT_TIMESTAMP`
columns and generated `experiments.updated_at` handle it). Remaining
`TODO(schema-migration)` markers in the code describe work that is already
done and can be cleaned up.

### Fixed: broken "Back to parent project" link
`experiment.php` linked to a non-existent `project.php?proj_ID=...` page. It
now links to `experiment_library.php?project_id=...`, which lists the
project's experiments and doubles as the parent project page.

### New TODO / NOTE markers
- `check_user_permission()`: TODO noting the schema has no explicit `owner`
  role on experiments (level 3 for an experiment only comes via project
  ownership) and a TODO on the Scriba-admin level (code returns 1,
  ARCHITECTURE.md says 3 — decide which is intended).
- NOTE comments on exception messages still using old identifier spellings
  (`Experiment_ID`, `Project_ID`, `Lab_Group_ID`, `User_ID`).
- NOTE in `save_experiment_section.php`: `$experiment_id` can be bound as
  `"i"` everywhere (it is `"s"` in the fetchers).
- TODOs in `create_profile.php` / `login.php` for the remaining registration
  and login work (`saved_changes` / `streak` values at registration,
  `login_log` rows for rate limiting, `last_login_at` on successful login).

### Schema and documentation
- `database_schema.sql` / `mock_data.sql`: note that `streak` (and
  `saved_changes`) are reset to 0 on profile anonymization/deletion where
  reasonable; the `profile_points` view also excludes deleted profiles.
- `ARCHITECTURE.md` corrected: experiments have no explicit `owner` role —
  the owner of an experiment is the owner of its parent project
  (`experiment_members.role` is `ENUM('edit', 'read')`), and `profile_points`
  counts done experiments through project owners.
- Mock data checked against the schema: table/column names, ENUM values,
  generated columns (never inserted), and retention windows all conform.

## 2026-09-21 — Experiment pages

Date: 2026-09-21

Summary of the website structure implemented on this branch, as a starting
point for writing your own report. Written in third person; adapt as needed.

## What was added

### Experiment section pages: Plan / Log / Result
Each experiment has three content sections (plan, log, result). They were
originally separate pages (`experiment_plan.php`,
`experiment_log.php`, `experiment_result.php`), which have since been
consolidated into a single `experiment_section.php` page that takes the
section name (`Plan` / `Log` / `Result`) as a `section` query parameter, so
a single set of helper scripts serves all sections.

### Section editing
On the plan page, users with edit permission (access level >= 2) get a form
with a textarea and a "Mark as Done" checkbox. Submitting posts to
`save_experiment_section.php` (formerly `exp_edit_section.php`), which saves
the text, updates the done flag, and
refreshes the "last updated" timestamp for that section. Users with read-only
access see the text as static content instead of the form.

### Progress tracking
Each section has a done flag shown as a checkbox badge (checked/empty box).
The experiment overview page (`experiment.php`) shows all three badges, and
each section page shows its own.

### Last-updated timestamps
Every section stores when it was last saved, displayed on the section page
as "Last updated". The experiment overview page also shows when the
experiment was created and last updated, plus the last-save time of each
section next to its progress badge. Timestamps are formatted for display
as `YYYY-MM-DD HH:MM`.

### Access control
- All pages re-check permission via `check_user_permission()`: level 1 or
  higher required to view, level 2 or higher to see the edit form.
- Both form-processing endpoints (`save_experiment_section.php`,
  `save_experiment_tags.php`) perform their own server-side permission check
  (level >= 2), so they cannot be bypassed by posting directly to them.
  An earlier version of the tag form carried the access level in a hidden
  input; this was replaced with the server-side check.
- The section name posted by the form is validated against a whitelist
  (Plan / Log / Result), preventing SQL injection through the column names.
- Both endpoints handle a missing experiment ID gracefully (message +
  redirect) instead of crashing.
- All database queries use prepared statements with bound parameters.
- All user-supplied output is passed through `htmlspecialchars()`.
- Missing data is handled defensively: a new experiment with no saved text
  shows an empty textarea rather than an error.

### User feedback (PRG pattern)
After saving, the user is redirected back to the section page, and
success/error messages collected during the save are passed through the
session and displayed once on the page they land on.

## Known limitations / future work
- ~~The log and result section pages are placeholders; only the plan page
  is implemented so far.~~ Resolved: the section pages were consolidated
  into `experiment_section.php`, which serves all three sections.
- ~~`check_user_permission()` only implements the 'experiment' entity type;
  project / lab / company are placeholders.~~ Resolved: all entity types
  ('experiment', 'project', 'lab', 'company', 'scriba') are implemented
  against the new schema.
- ~~The database connection uses local credentials and an empty database name
  in `database/db.php`.~~ Partially resolved: the database name is set
  (`scriba_db`, matching `database_schema.sql`), but credentials are still
  local and need configuration before deployment.
