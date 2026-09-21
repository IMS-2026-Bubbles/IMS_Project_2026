# Architecture

IMS (Inventory/Experiment Management System) is a plain PHP website with no
framework. Every page is a PHP script that runs top-to-bottom and renders its
own HTML. The structure below describes how files are organized and the
patterns the code follows.

## Folder layout

```
/                           Page scripts (one PHP file per page)
├── index.php               Login / start page
├── experiment.php          Experiment overview page
├── experiment_plan.php     Experiment plan section (same pattern for log/result)
├── project_library.php     Listing pages, profile pages, etc.
│
├── Session/                Session handling
│   ├── init.php            Starts the session (guarded, safe to include anywhere)
│   └── check_user_logged_in.php
│
├── Database_related/       Database connection
│   ├── db.php              Opens $conn (mysqli)
│   └── closeDB.php         Closes $conn
│
└── Functional_php/         Shared functionality
    ├── user_permission.php     check_user_permission(): access level per entity
    ├── navbar.php, style.css   Layout and styling
    └── exp_helpers/         Experiment-related includes, split by purpose:
        ├── exp_fetch_*.php      Fetch data (tags, progress, text, timestamps)
        ├── exp_edit_*.php      Process form POSTs and redirect
        └── exp_progress_fun.php Helper function returning HTML for a progress badge
```

## Request flow

Every page script follows the same order:

1. **Session**: include `Session/init.php` (and the logged-in check where needed).
2. **Input**: read parameters from `$_GET` / `$_POST` with `??` defaults.
3. **Database**: include `Database_related/db.php` to get `$conn`.
4. **Permission check**: `check_user_permission($conn, $_SESSION['user_id'], <type>, <ID>)`
   returns an access level: 0 = none, 1 = read, 2 = edit, 3 = owner.
   Pages block on `< 1`; write endpoints block on `< 2`.
5. **Fetch and render**: include `exp_fetch_*.php` helpers to load data, then
   echo the HTML for the page.

## Patterns

### Includes as data-fetch steps
Fetcher files (`exp_fetch_*.php`) are not functions. They are included inline,
expect variables like `$exp_ID` to already exist, and leave their result in a
named variable (`$exp_tags_array`, `$exp_progress_flags`, `$exp_section_text`,
`$exp_last_update`). Each file documents the expected input and returned
variable in its header comment.

### POST-Redirect-Get (PRG) with session messages
Write endpoints (`exp_edit_section.php`, `exp_edit_tags.php`) never render a
page. They:
1. Collect feedback strings in a local `$messages` array.
2. Store it in the session under a page-specific key
   (`$_SESSION['messages_exp_edit_section']`).
3. `header("Location: ...")` + `exit()` back to the referring page.

The target page retrieves the message array from the session, unsets it, and
prints it. This keeps refresh/reload from resubmitting forms and gives the
user simple success/error feedback.

### Access levels
Access levels come from one central query in `user_permission.php` that walks
the membership hierarchy (company → lab group → project → experiment) and
returns the highest applicable level. UI adapts to the level: view-only users
get static text, users with level >= 2 get the edit form.

### Database access
All queries use **mysqli prepared statements**. Values are always bound with
`bind_param()`. Column names that depend on input (e.g. the section name
`Plan`/`Log`/`Result`) are never taken raw from the request: they are checked
against a whitelist (`$valid_sections`) before being used in SQL.

### Output escaping
Anything echoed into HTML goes through `htmlspecialchars()` to prevent XSS
(this includes hidden input values, textarea content, and badge labels).
`urlencode()` is used when building query strings in links/redirects.
