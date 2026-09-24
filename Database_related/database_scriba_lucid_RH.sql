-- Naming conventions: (https://dev.to/ovid/database-naming-standards-2061)
  -- snake_case for table and column names
  -- Tables are plural nouns, columns are singular nouns
  -- no abbreviations, no acronyms, no reserved words
  -- use descriptive names, avoid generic names like "data" or "info" or "id"
  -- name FK same as the PK it references
  -- only keys include the table name in the column name if it is ambiguous

-- Data types:
  -- PK get auto increment int, except for company and lab which are 
    -- varchar(10) including a prefix (e.g. "c" (company) or "l" (lab)) to avoid collisions
  -- VARCHAR without a length constraint is 255 (names, emails, passwords, tags)
  -- Timestamps are used for created and updated columns, with default value of CURRENT_TIMESTAMP
  -- Boolean columns are used for done columns, with default value of FALSE

CREATE TABLE `companies` (
  `company_id` VARCHAR(10) UNIQUE,
  `name` VARCHAR(255),
  PRIMARY KEY (`company_id`)
);

CREATE TABLE `labs` (
  `lab_id` VARCHAR(10) UNIQUE,
  `company_id` VARCHAR(10) NOT NULL,
  `name` VARCHAR(255),
  PRIMARY KEY (`lab_id`),
  FOREIGN KEY (`company_id`)
      REFERENCES `companies`(`company_id`)
);

CREATE TABLE `projects` (
  `project_id` INT AUTO_INCREMENT,
  `name` VARCHAR(255),
  `lab_id` VARCHAR(10),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  -- Last update to the project row, see project_activity.last_updated_at
  -- for the most recent update to the project or any of its experiments
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_done` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`project_id`),
  FOREIGN KEY (`lab_id`)
      REFERENCES `labs`(`lab_id`)
);

CREATE TABLE `profiles` (
  `profile_id` INT AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `first_name` VARCHAR(255),
  `last_name` VARCHAR(255),
  `salt` BINARY(16) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `saved_changes` INT,
  `last_login_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Update manually on login
  `streak` INT,
  `is_scriba_admin` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`profile_id`)
);

CREATE TABLE `project_members` (
  `project_id` INT,
  `profile_id` INT,
  `role` ENUM('owner', 'edit', 'read') NOT NULL,
  PRIMARY KEY (`project_id`, `profile_id`),
  FOREIGN KEY (`project_id`)
      REFERENCES `projects`(`project_id`),
  FOREIGN KEY (`profile_id`)
      REFERENCES `profiles`(`profile_id`)
);

CREATE TABLE `project_tags` (
  `project_id` INT,
  `tag` VARCHAR(255),
  PRIMARY KEY (`project_id`, `tag`),
  FOREIGN KEY (`project_id`)
      REFERENCES `projects`(`project_id`)
);

CREATE TABLE `experiments` (
  `experiment_id` INT AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `project_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `plan_text` TEXT,
  `plan_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `plan_is_done` BOOLEAN NOT NULL DEFAULT FALSE,
  `log_text` TEXT,
  `log_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `log_is_done` BOOLEAN NOT NULL DEFAULT FALSE,
  `result_text` TEXT,
  `result_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `result_is_done` BOOLEAN NOT NULL DEFAULT FALSE,
  `updated_at` DATETIME -- GENERATED ALWAYS AS = automagic, generated column that shows the most recent update time for the experiment
    GENERATED ALWAYS AS (
      GREATEST(
        COALESCE(`plan_updated_at`, '1970-01-01 00:00:00'),
        COALESCE(`log_updated_at`, '1970-01-01 00:00:00'),
        COALESCE(`result_updated_at`, '1970-01-01 00:00:00')
      )
    ) STORED, -- Stored vs Virtual: Stored is faster for queries but takes more space.
                -- Stored is physically stored in the table, 
                -- Virtual is calculated on the fly.
  `is_done` BOOLEAN -- GENERATED ALWAYS AS = automagic, generated column that indicates whether the experiment is done based on the status of plan, log, and result
    GENERATED ALWAYS AS (
      `plan_is_done` AND `log_is_done` AND `result_is_done`
    ) STORED,
  PRIMARY KEY (`experiment_id`),
  FOREIGN KEY (`project_id`)
      REFERENCES `projects`(`project_id`)
);

CREATE TABLE `experiment_tags` (
  `experiment_id` INT,
  `tag` VARCHAR(255),
  PRIMARY KEY (`experiment_id`, `tag`),
  FOREIGN KEY (`experiment_id`)
      REFERENCES `experiments`(`experiment_id`)
);

CREATE TABLE `company_members` (
  `company_id` VARCHAR(10),
  `profile_id` INT,
  `role` ENUM('admin', 'member') NOT NULL,
  PRIMARY KEY (`company_id`, `profile_id`),
  FOREIGN KEY (`profile_id`)
      REFERENCES `profiles`(`profile_id`),
  FOREIGN KEY (`company_id`)
      REFERENCES `companies`(`company_id`)
);

CREATE TABLE `experiment_members` (
  `experiment_id` INT,
  `profile_id` INT,
  `role` ENUM('edit', 'read') NOT NULL,
  PRIMARY KEY (`experiment_id`, `profile_id`),
  FOREIGN KEY (`profile_id`)
      REFERENCES `profiles`(`profile_id`),
  FOREIGN KEY (`experiment_id`)
      REFERENCES `experiments`(`experiment_id`)
);

CREATE TABLE `lab_members` (
  `lab_id` VARCHAR(10),
  `profile_id` INT,
  `role` ENUM('admin', 'member') NOT NULL,
  PRIMARY KEY (`lab_id`, `profile_id`),
  FOREIGN KEY (`lab_id`)
      REFERENCES `labs`(`lab_id`),
  FOREIGN KEY (`profile_id`)
      REFERENCES `profiles`(`profile_id`)
);

-- Activity log table to track user actions (when logged in)
-- Track: 
  -- create, update, delete actions on projects and experiments
  -- permission changes on projects and experiments
  -- member/lab additions and removals on labs and companies
-- Retention: Keep last 90 days, delete older automatically
CREATE TABLE `activity_log` (
  `activity_id` INT AUTO_INCREMENT, -- PK id
  `profile_id` INT NOT NULL, -- who did the action
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- when the action happened
  `entity_type` ENUM( -- what was the action performed on (project/experiment/profile/company/lab)
      'project', 'experiment', 
      'profile', 'company', 'lab'
  ) NOT NULL,
  `entity_id` VARCHAR(20) NOT NULL, -- id of the project/experiment/profile/company acted upon
  `activity_type` ENUM( -- what action was performed
      'create', 'update', 'delete', -- project/experiment actions
      'change_role', 'change_permission', -- profile role/access permission actions
      'add_member', 'remove_member', -- membership actions
      'add_lab', 'remove_lab' -- lab/company actions
  ) NOT NULL,
  `detail` VARCHAR(255), -- additional details about the action, 
    -- e.g. which field was updated, which member was added/removed, etc.
  PRIMARY KEY (`activity_id`),
  FOREIGN KEY (`profile_id`)
      REFERENCES `profiles`(`profile_id`)
);
-- Index for activity on project/experiment
  -- SELECT *
  -- FROM `activity_log`
  -- WHERE `entity_type` = ? AND `entity_id` = ? 
CREATE INDEX `idx_activity_log_entity` 
  ON `activity_log` (`entity_type`, `entity_id`, `created_at`);
-- Index for activity by profile
  -- SELECT *
  -- FROM `activity_log`
  -- WHERE `profile_id` = ?
CREATE INDEX `idx_activity_log_profile` 
  ON `activity_log` (`profile_id`, `created_at`);

-- Login log table to track user login (success & failure)
-- Track:
  -- success/failure of login attempt
  -- profile_id (nullable), email
  -- timestamp of login attempt
  -- IP address of login attempt
-- Retention: Keep last 90 days, delete older automatically
CREATE TABLE `login_log` (
  `login_id` INT AUTO_INCREMENT, -- PK id
  `profile_id` INT, -- nullable, if login failed, profile_id is null
  `email` VARCHAR(255) NOT NULL, -- email used for login attempt
  `login_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- when the login attempt happened
  `ip_address` VARCHAR(45) NOT NULL, -- IP address of login attempt (IPv4 or IPv6)
  `success` BOOLEAN NOT NULL, -- whether the login attempt was successful
  PRIMARY KEY (`login_id`),
  FOREIGN KEY (`profile_id`)
      REFERENCES `profiles`(`profile_id`)
);
-- Check failed attempts for rate limit/lockout (last 15 minutes)
  -- SELECT COUNT(*) 
  -- FROM `login_log`
  -- WHERE `email` = ? AND `success` = FALSE AND `login_at` > NOW() - INTERVAL 15 MINUTE;
CREATE INDEX `idx_login_log_attempt`
  ON `login_log` (`email`, `login_at`);
-- Show login history for a profile
  -- SELECT *
  -- FROM `login_log`
  -- WHERE `profile_id` = ?
CREATE INDEX `idx_login_log_profile`
  ON `login_log` (`profile_id`, `login_at`);

-- Table = project_updates, 
-- Columns = project_id,
          -- lab_id, 
          -- created_at, 
          -- updated_at, 
          -- is_done, 
          -- last_updated_at (most recent update inside the project)
CREATE VIEW `project_updates` AS
SELECT
    `projects`.`project_id`,
    `projects`.`lab_id`,
    `projects`.`created_at`,
    `projects`.`updated_at`,
    `projects`.`is_done`,
    GREATEST(
      COALESCE(MAX(`experiments`.`updated_at`), '1970-01-01 00:00:00'),
      COALESCE(`projects`.`updated_at`, '1970-01-01 00:00:00')
    ) AS `last_updated_at`
FROM `projects`
LEFT JOIN `experiments`
    ON `projects`.`project_id` = `experiments`.`project_id`
GROUP BY `projects`.`project_id`;

-- Table = profile_points, 
-- Columns = profile_id, 
          -- saved_changes, 
          -- streak, 
          -- count_done_projects, 
          -- count_done_experiments, 
          -- scriba_points
CREATE VIEW `profile_points` AS
SELECT 
    `profiles`.`profile_id`,
    `profiles`.`saved_changes`,
    `profiles`.`streak`,
    `done_projects`.`count_done_projects`,
    `done_experiments`.`count_done_experiments`,
    COALESCE(`saved_changes`, 0)
      + COALESCE(`streak`, 0)
      + 100 * COALESCE(`done_projects`.`count_done_projects`, 0) 
      + 10 * COALESCE(`done_experiments`.`count_done_experiments`, 0)
      AS `scriba_points`
FROM `profiles`
LEFT JOIN ( -- Count the number of completed projects for each profile who is an owner
  SELECT `project_members`.`profile_id`, COUNT(*) AS `count_done_projects`
  FROM `project_members`
  JOIN `projects` 
    ON `project_members`.`project_id` = `projects`.`project_id`
  WHERE `project_members`.`role` = 'owner'
    AND `projects`.`is_done` = TRUE
  GROUP BY `project_members`.`profile_id`
) `done_projects` -- name of this new subquery table 
  ON `profiles`.`profile_id` = `done_projects`.`profile_id`
LEFT JOIN ( -- Count the number of completed experiments for each user who is an owner (experiment owner is the project owner)
  SELECT `project_members`.`profile_id`, COUNT(*) AS `count_done_experiments`
  FROM `project_members`
  JOIN `experiments` 
    ON `project_members`.`project_id` = `experiments`.`project_id`
  WHERE `project_members`.`role` = 'owner'
    AND `experiments`.`is_done` = TRUE
  GROUP BY `project_members`.`profile_id`
) `done_experiments` -- name of this new subquery table
  ON `profiles`.`profile_id` = `done_experiments`.`profile_id`;
