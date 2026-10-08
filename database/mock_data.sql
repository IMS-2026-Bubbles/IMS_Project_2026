-- Mock example data for the Scriba schema.
-- Run AFTER database_schema.sql (same database: scriba_db).

-- Notes:
  -- All primary keys are INT AUTO_INCREMENT (including companies and labs).
  -- IDs are inserted explicitly here so the rows can reference each other;
  -- AUTO_INCREMENT continues after the highest inserted ID for new rows.
  -- Passwords are PLACEHOLDER bcrypt-format strings and cannot be used to
  -- log in. To make demo accounts usable, generate real hashes and paste
  -- them into the profiles INSERT below, e.g.:
  --   php -r "echo password_hash('ChangeMe!2026', PASSWORD_BCRYPT);"
  -- All demo accounts have is_verified = TRUE so email verification does
  -- not block login once real hashes are in place.
  -- The dataset intentionally covers documented edge cases:
    -- profile 5 is GDPR-anonymized (is_deleted, kept memberships, still
    --    owns project 3 -> admin ownership-transfer case)
    -- lab 2 has no projects (empty lab)
    -- profile 8 has read-only access everywhere (incl. an access_denied entry)
    -- login_log contains failed logins and a rate-limit burst
  -- activity_log and login_log timestamps are RELATIVE to the moment this
  -- script runs (@now). This keeps them inside the 90-day retention window
  -- and keeps the rate-limit burst inside the 15-minute lockout window
  -- right after loading. Projects/experiments use fixed timestamps.
  -- experiments.updated_at and experiments.is_done are generated columns:
  -- they are NOT inserted here.
  -- Experiment *_updated_at columns that are NULL rely on MySQL 8 default
  -- behaviour (explicit_defaults_for_timestamp = ON) so NULL is stored.

USE scriba_db;

SET @now = NOW();

-- ---------------------------------------------------------------------------
-- Companies and labs
-- ---------------------------------------------------------------------------

INSERT INTO `companies` (`company_id`, `name`) VALUES
    (1, 'Asteria Biotech'),
    (2, 'Helix Environmental Labs');

INSERT INTO `labs` (`lab_id`, `company_id`, `name`) VALUES
    (1, 1, 'Molecular Biology Lab'),
    (2, 1, 'Analytical Chemistry Lab'),   -- no projects: empty lab
    (3, 2, 'Water Quality Lab');

-- ---------------------------------------------------------------------------
-- Profiles (users)
-- ---------------------------------------------------------------------------

-- Placeholder hash (not a real bcrypt digest of anything):
--   $2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM
INSERT INTO `profiles`
    (`profile_id`, `email`, `first_name`, `last_name`, `password`,
     `agreed_to_tos`, `saved_changes`, `last_login_at`, `streak`,
     `is_verified`, `verify_token`, `is_scriba_admin`, `is_deleted`)
VALUES
    (1, 'alice.chen@example.com', 'Alice', 'Chen',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 128, @now - INTERVAL 26 HOUR, 12, TRUE, NULL, FALSE,  FALSE),
    (2, 'bob.martinez@example.com', 'Bob', 'Martinez',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 86,  @now - INTERVAL 25 HOUR, 7,  TRUE, NULL, FALSE, FALSE),
    (3, 'carla.rossi@example.com', 'Carla', 'Rossi',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 210, @now - INTERVAL 48 HOUR, 21, TRUE, NULL, FALSE, FALSE),
    (4, 'david.kim@example.com', 'David', 'Kim',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 64,  @now - INTERVAL 24 HOUR, 3,  TRUE, NULL, FALSE, FALSE),
    (5, 'deleted_5@example.com', 'Deleted', 'Deleted',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKK',
     TRUE, 0,   @now - INTERVAL 100 HOUR, 0, TRUE, NULL, FALSE, TRUE),  -- anonymized (GDPR); streak and saved_changes reset to 0 on deletion
    (6, 'eva.novak@example.com', 'Eva', 'Novak',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 142, @now - INTERVAL 27 HOUR, 15, TRUE, NULL, FALSE, FALSE),
    (7, 'grace.liu@example.com', 'Grace', 'Liu',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 38,  @now - INTERVAL 50 HOUR, 5,  TRUE, NULL, FALSE, FALSE),
    (8, 'henry.adams@example.com', 'Henry', 'Adams',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 9,   @now - INTERVAL 72 HOUR, 1,  TRUE, NULL, FALSE, FALSE);

-- ---------------------------------------------------------------------------
-- Memberships
-- ---------------------------------------------------------------------------

INSERT INTO `company_members` (`company_id`, `profile_id`, `role`) VALUES
    (1, 1, 'member'),
    (1, 2, 'admin'),
    (1, 3, 'member'),
    (1, 4, 'member'),
    (1, 5, 'member'),   -- kept after anonymization
    (1, 6, 'member'),
    (2, 7, 'member'),
    (2, 8, 'member');
-- Company 2 admin intentionally missing: assign one when testing admin flows.

INSERT INTO `lab_members` (`lab_id`, `profile_id`, `role`) VALUES
    (1, 3, 'member'),
    (1, 4, 'admin'),
    (1, 5, 'member'),   -- kept after anonymization
    (1, 6, 'member'),
    (3, 7, 'admin'),
    (3, 8, 'member');
-- Lab 2 has no members or projects yet.

-- project_members and experiment_members are inserted further below,
-- after the projects/experiments they reference exist.

-- ---------------------------------------------------------------------------
-- Projects
-- ---------------------------------------------------------------------------

INSERT INTO `projects`
    (`project_id`, `name`, `lab_id`, `created_at`, `updated_at`, `is_done`)
VALUES
    (1, 'Enzyme Kinetics Study',          1, '2026-08-10 09:15:00', '2026-09-15 17:40:00', FALSE),
    (2, 'Protein Purification Protocol',  1, '2026-07-01 10:00:00', '2026-09-23 12:30:00', TRUE),   -- all experiments done
    (3, 'Assay Validation',               1, '2026-09-01 08:45:00', '2026-09-22 15:10:00', FALSE),
    (4, 'Groundwater Contaminant Screen', 3, '2026-08-20 07:30:00', '2026-09-18 16:20:00', FALSE),
    (5, 'Microplastics Survey',           3, '2026-09-05 09:00:00', '2026-09-23 11:05:00', FALSE);

INSERT INTO `project_tags` (`project_id`, `tag`) VALUES
    (1, 'kinetics'),
    (1, 'enzyme'),
    (2, 'chromatography'),
    (2, 'protocol'),
    (3, 'validation'),
    (4, 'fieldwork'),
    (5, 'microplastics'),
    (5, 'survey');

INSERT INTO `project_members` (`project_id`, `profile_id`, `role`) VALUES
    (1, 3, 'owner'),
    (1, 4, 'read'),
    (1, 6, 'edit'),
    (2, 3, 'owner'),
    (2, 6, 'edit'),
    (3, 5, 'owner'),      -- deleted profile still owns project 3 (admin must transfer)
    (3, 4, 'edit'),
    (4, 7, 'owner'),
    (4, 8, 'read'),
    (5, 7, 'owner'),
    (5, 8, 'read');

-- ---------------------------------------------------------------------------
-- Experiments
-- (updated_at / is_done are generated columns -- not inserted)
-- ---------------------------------------------------------------------------

INSERT INTO `experiments`
    (`experiment_id`, `name`, `project_id`, `created_at`,
     `plan_text`, `plan_updated_at`, `plan_is_done`,
     `log_text`, `log_updated_at`, `log_is_done`,
     `result_text`, `result_updated_at`, `result_is_done`)
VALUES
    (1, 'Amylase activity across pH gradients', 1, '2026-08-11 10:20:00',
     'Measure amylase activity in buffer series from pH 4 to pH 10 at 25C. Triplicates per pH, iodine-starch endpoint assay.',
     '2026-08-11 10:25:00', TRUE,
     'Runs at pH 4-7 completed. Activity peaks near pH 6.5; runs at pH 8-10 pending reagent restock.',
     '2026-09-12 14:50:00', FALSE,
     NULL, NULL, FALSE),
    (2, 'Substrate concentration series', 1, '2026-08-25 09:00:00',
     'Vary substrate 0.1-10 mM at fixed enzyme concentration; fit Michaelis-Menten parameters.',
     '2026-08-25 09:05:00', TRUE,
     'All 12 concentrations measured in triplicate. Raw rates tabulated in lab notebook p.34.',
     '2026-09-14 18:05:00', TRUE,
     'Km = 1.8 mM, Vmax = 0.42 umol/min. Lineweaver-Burk fit consistent with direct fit.',
     '2026-09-15 17:40:00', TRUE),
    (3, 'Column cleanup run 3', 2, '2026-08-03 13:15:00',
     'Repeat size-exclusion cleanup with adjusted salt gradient per protocol revision B2.',
     '2026-08-03 13:20:00', TRUE,
     'Gradient 150-400 mM over 40 min. Peak broadening reduced vs run 2.',
     '2026-09-18 09:45:00', TRUE,
     'Fractions 12-18 pooled; purity >95% by SDS-PAGE. Protocol approved.',
     '2026-09-20 12:30:00', TRUE),
    (4, 'Repeat purification with lower load', 2, '2026-09-21 10:10:00',
     'Reduce column load by 50% to check resolution improvement observed in run 3.',
     '2026-09-21 10:15:00', TRUE,
     'Load reduced to 50%; peaks baseline-separated, resolution improved over run 3.',
     '2026-09-22 10:30:00', TRUE,
     'Improvement confirmed; reduced load adopted as standard in the protocol.',
     '2026-09-23 12:30:00', TRUE),
    (5, 'Inter-lab reproducibility run', 3, '2026-09-10 11:00:00',
     'Send matched aliquots to partner lab; compare ELISA standard curves within 10%.',
     '2026-09-10 11:05:00', TRUE,
     'First batch shipped; awaiting partner results.',
     '2026-09-22 15:10:00', FALSE,
     NULL, NULL, FALSE),
    (6, 'Well sampling round 1', 4, '2026-08-21 08:00:00',
     'Sample 6 monitoring wells, duplicate field blanks, chain-of-custody forms.',
     '2026-08-21 08:05:00', TRUE,
     'All wells sampled; pH/temp logged on site. One field blank flagged for review.',
     '2026-09-18 16:20:00', TRUE,
     NULL, NULL, FALSE),
    (7, 'Well sampling round 2', 4, '2026-09-19 08:00:00',
     'Repeat round 1 with revised decontamination steps between wells.',
     '2026-09-19 08:05:00', TRUE,
     NULL, NULL, FALSE,
     NULL, NULL, FALSE),
    (8, 'Beach sediment transects', 5, '2026-09-06 07:30:00',
     'Three 100 m transects; 250 g composite samples every 10 m, sieved to 5 mm.',
     '2026-09-06 07:35:00', TRUE,
     'Transects 1-2 complete; transect 3 interrupted by weather, resuming Friday.',
     '2026-09-23 11:05:00', FALSE,
     NULL, NULL, FALSE);

INSERT INTO `experiment_tags` (`experiment_id`, `tag`) VALUES
    (1, 'ph-gradient'),
    (1, 'amylase'),
    (2, 'michaelis-menten'),
    (3, 'fplc'),
    (5, 'reproducibility'),
    (6, 'sampling'),
    (7, 'sampling'),
    (8, 'transect'),
    (8, 'sediment');

INSERT INTO `experiment_members` (`experiment_id`, `profile_id`, `role`) VALUES
    (1, 4, 'read'),
    (1, 6, 'edit'),
    (2, 3, 'edit'),
    (5, 4, 'edit'),
    (6, 7, 'edit'),
    (6, 8, 'read'),
    (8, 7, 'edit');

-- ---------------------------------------------------------------------------
-- Activity log (only authenticated actions; profile_id NOT NULL in practice)
-- entity_id is INT and holds the numeric ID of the entity.
-- Timestamps are relative to @now so they stay inside the 90-day retention.
-- ---------------------------------------------------------------------------

INSERT INTO `activity_log`
    (`profile_id`, `acted_at`, `entity_type`, `entity_id`, `activity_type`, `detail`)
VALUES
    (1, @now - INTERVAL 80 DAY,  'company',    1, 'add_lab',       'Added lab Molecular Biology Lab'),
    (1, @now - INTERVAL 79 DAY,  'company',    2, 'add_company',   'Registered company Helix Environmental Labs'),
    (3, @now - INTERVAL 59 DAY,  'project',    1, 'create',        'Created project Enzyme Kinetics Study'),
    (3, @now - INTERVAL 58 DAY,  'experiment', 1, 'create',        'Created experiment Amylase activity across pH gradients'),
    (3, @now - INTERVAL 44 DAY,  'experiment', 2, 'create',        'Created experiment Substrate concentration series'),
    (6, @now - INTERVAL 26 DAY,  'experiment', 1, 'update',        'Updated log text for experiment 1'),
    (3, @now - INTERVAL 24 DAY,  'experiment', 2, 'update',        'Completed log for experiment 2'),
    (5, @now - INTERVAL 28 DAY,  'experiment', 5, 'create',        'Created experiment Inter-lab reproducibility run'),
    (4, @now - INTERVAL 16 DAY,  'project',    3, 'update',        'Renamed project Assay Validation'),
    (7, @now - INTERVAL 20 DAY,  'experiment', 6, 'update',        'Completed log for Well sampling round 1'),
    (7, @now - INTERVAL 15 DAY,  'experiment', 8, 'update',        'Updated log text for Beach sediment transects'),
    (8, @now - INTERVAL 5 DAY,   'experiment', 1, 'access_denied', 'Read-only member attempted to edit experiment 1'),
    (5, @now - INTERVAL 99 HOUR, 'profile',    5, 'delete',        'Profile anonymized per GDPR request');

-- ---------------------------------------------------------------------------
-- Login log (nullable profile_id: unknown emails / pre-deletion failures)
-- The burst of failures for alice.chen is within the last 15 minutes after
-- loading, so the lockout query in the schema returns 3.
-- ---------------------------------------------------------------------------

INSERT INTO `login_log`
    (`profile_id`, `email`, `login_at`, `ip_address`, `success`, `detail`)
VALUES
    (3,    'carla.rossi@example.com',  @now - INTERVAL 48 HOUR,  '192.168.1.23', TRUE,  NULL),
    (4,    'david.kim@example.com',    @now - INTERVAL 24 HOUR,  '192.168.1.41', TRUE,  NULL),
    (6,    'eva.novak@example.com',    @now - INTERVAL 27 HOUR,  '192.168.1.58', TRUE,  NULL),
    (7,    'grace.liu@example.com',    @now - INTERVAL 50 HOUR,  '10.0.0.14',    TRUE,  NULL),
    (5,    'deleted_5@example.com',    @now - INTERVAL 100 HOUR, '192.168.1.72', TRUE,  'Last login before anonymization'),
    (NULL, 'unknown@example.com',      @now - INTERVAL 5 HOUR,   '203.0.113.7',  FALSE, 'Email not found'),
    (NULL, 'deleted_5@example.com',    @now - INTERVAL 295 MINUTE, '203.0.113.7', FALSE, 'Profile deleted; anonymized email kept'),
    (NULL, 'alice.chen@example.com',   @now - INTERVAL 10 MINUTE, '203.0.113.7', FALSE, 'Incorrect password'),   -- rate-limit demo: burst of 3 failures
    (NULL, 'alice.chen@example.com',   @now - INTERVAL 8 MINUTE,  '203.0.113.7', FALSE, 'Incorrect password'),   -- within 15 minutes
    (NULL, 'alice.chen@example.com',   @now - INTERVAL 6 MINUTE,  '203.0.113.7', FALSE, 'Incorrect password');   -- within 15 minutes