# NEWS — Experiment_pages branch

Date: 2026-09-21

Summary of the website structure implemented on this branch, as a starting
point for writing your own report. Written in third person; adapt as needed.

## What was added

### Experiment section pages: Plan / Log / Result
Each experiment has three content sections (plan, log, result) with their own
page (`experiment_plan.php`, `experiment_log.php`, `experiment_result.php`).
All three follow the same structure, with the section name
(`Plan` / `Log` / `Result`) as the only difference, so a single set of helper
scripts serves all pages.

### Section editing
On the plan page, users with edit permission (access level >= 2) get a form
with a textarea and a "Mark as Done" checkbox. Submitting posts to
`exp_edit_section.php`, which saves the text, updates the done flag, and
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
- Both form-processing endpoints (`exp_edit_section.php`,
  `exp_edit_tags.php`) perform their own server-side permission check
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
- The log and result section pages (`experiment_log.php`,
  `experiment_result.php`) are placeholders; only the plan page is
  implemented so far.
- `check_user_permission()` only implements the 'experiment' entity type;
  project / lab / company are placeholders.
- The database connection uses local credentials and an empty database name
  in `Database_related/db.php`; this needs configuration before deployment.
