# NEWS — Experiment_pages branch

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
as "Last updated".

### Access control
- All pages re-check permission via `check_user_permission()`: level 1 or
  higher required to view, level 2 or higher to see the edit form.
- The form-processing endpoint (`exp_edit_section.php`) performs its own
  permission check (level >= 2), so it cannot be bypassed by posting
  directly to it.
- The section name posted by the form is validated against a whitelist
  (Plan / Log / Result), preventing SQL injection through the column names.
- All database queries use prepared statements with bound parameters.
- All user-supplied output is passed through `htmlspecialchars()`.

### User feedback (PRG pattern)
After saving, the user is redirected back to the section page, and
success/error messages collected during the save are passed through the
session and displayed once on the page they land on.

## Known limitations / future work
- `experiment.php` does not yet read `exp_ID` from the URL
  (`$_GET['exp_ID']`), so the redirect target loses the experiment context.
- `check_user_permission()` only implements the 'experiment' entity type;
  project / lab / company are placeholders.
- The database connection uses local credentials and an empty database name
  in `Database_related/db.php`; this needs configuration before deployment.
