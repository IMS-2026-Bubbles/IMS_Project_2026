-- Mock example data for the Scriba schema.
-- Run AFTER database_schema.sql (same database: scriba_db).

-- Notes:
  -- Passwords are PLACEHOLDER bcrypt-format strings and cannot be used to
  -- log in. To make demo accounts usable, generate real hashes and paste
  -- them into the profiles INSERT below, e.g.:
  --   php -r "echo password_hash('ChangeMe!2026', PASSWORD_BCRYPT);"
  -- The salt column is BINARY(16): distinct dummy values via UNHEX().
  -- The dataset intentionally covers documented edge cases:
    -- profile 5 is GDPR-anonymized (is_deleted, kept memberships, still
    --    owns project 3 -> admin ownership-transfer case)
    -- lab l2 has no projects (empty lab)
    -- profile 8 has read-only access everywhere
    -- login_log contains failed logins and a rate-limit burst
  -- Timestamps fall inside the 90-day retention window around 2026-09-24.
  -- experiments.updated_at and experiments.is_done are generated columns:
  -- they are NOT inserted here.

USE scriba_db;

-- ---------------------------------------------------------------------------
-- Companies and labs
-- ---------------------------------------------------------------------------

INSERT INTO `companies` (`company_id`, `name`) VALUES
    ('c1', 'Asteria Biotech'),
    ('c2', 'Helix Environmental Labs');

INSERT INTO `labs` (`lab_id`, `company_id`, `name`) VALUES
    ('l1', 'c1', 'Molecular Biology Lab'),
    ('l2', 'c1', 'Analytical Chemistry Lab'),   -- no projects: empty lab
    ('l3', 'c2', 'Water Quality Lab');

-- ---------------------------------------------------------------------------
-- Profiles (users)
-- ---------------------------------------------------------------------------

-- Placeholder hash (not a real bcrypt digest of anything):
--   $2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM
INSERT INTO `profiles`
    (`profile_id`, `email`, `first_name`, `last_name`, `salt`, `password`,
     `agreed_to_toc`, `saved_changes`, `last_login_at`, `streak`,
     `is_scriba_admin`, `is_deleted`)
VALUES
    (1, 'alice.chen@example.com', 'Alice', 'Chen',
     UNHEX('0011223344556677889900aabbccdde0'),
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 128, '2026-09-23 08:30:00', 12, TRUE,  FALSE),
    (2, 'bob.martinez@example.com', 'Bob', 'Martinez',
     UNHEX('11223344556677889900aabbccdde0f0'),
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 86,  '2026-09-23 09:10:00', 7,  FALSE, FALSE),
    (3, 'carla.rossi@example.com', 'Carla', 'Rossi',
     UNHEX('223344556677889900aabbccdde0f001'),
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 210, '2026-09-22 14:05:00', 21, FALSE, FALSE),
    (4, 'david.kim@example.com', 'David', 'Kim',
     UNHEX('3344556677889900aabbccdde0f00112'),
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 64,  '2026-09-23 11:45:00', 3,  FALSE, FALSE),
    (5, 'deleted_5@example.com', 'Deleted', 'Deleted',
     UNHEX('44556677889900aabbccdde0f0011223'),
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKK',
     TRUE, 95,  '2026-09-20 16:00:00', 0,  FALSE, TRUE),  -- anonymized (GDPR)
    (6, 'eva.novak@example.com', 'Eva', 'Novak',
     UNHEX('0a0b0c0d0e0f10111213141516171819'),
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 142, '2026-09-23 10:20:00', 15, FALSE, FALSE),
    (7, 'grace.liu@example.com', 'Grace', 'Liu',
     UNHEX('1a1b1c1d1e1f20212223242526272829'),
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 38,  '2026-09-22 08:55:00', 5,  FALSE, FALSE),
    (8, 'henry.adams@example.com', 'Henry', 'Adams',
     UNHEX('2a2b2c2d2e2f30313233343536373839'),
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 9,   '2026-09-21 13:30:00', 1,  FALSE, FALSE);

-- ---------------------------------------------------------------------------
-- Memberships
-- ---------------------------------------------------------------------------

INSERT INTO `company_members` (`company_id`, `profile_id`, `role`) VALUES
    ('c1', 1, 'member'),
    ('c1', 2, 'admin'),
    ('c1', 3, 'member'),
    ('c1', 4, 'member'),
    ('c1', 5, 'member'),   -- kept after anonymization
    ('c1', 6, 'member'),
    ('c2', 7, 'member'),
    ('c2', 8, 'member');
-- c2 admin intentionally missing: assign one when testing admin flows.

INSERT INTO `lab_members` (`lab_id`, `profile_id`, `role`) VALUES
    ('l1', 3, 'member'),
    ('l1', 4, 'admin'),
    ('l1', 5, 'member'),   -- kept after anonymization
    ('l1', 6, 'member'),
    ('l3', 7, 'admin'),
    ('l3', 8, 'member');
-- l2 has no members or projects yet.

-- project_members and experiment_members are inserted further below,
-- after the projects/experiments they reference exist.

-- ---------------------------------------------------------------------------
-- Projects
-- ---------------------------------------------------------------------------

INSERT INTO `projects`
    (`project_id`, `name`, `lab_id`, `created_at`, `updated_at`, `is_done`)
VALUES
    (1, 'Enzyme Kinetics Study',          'l1', '2026-08-10 09:15:00', '2026-09-15 17:40:00', FALSE),
    (2, 'Protein Purification Protocol',  'l1', '2026-07-01 10:00:00', '2026-09-20 12:30:00', TRUE),
    (3, 'Assay Validation',               'l1', '2026-09-01 08:45:00', '2026-09-22 15:10:00', FALSE),
    (4, 'Groundwater Contaminant Screen', 'l3', '2026-08-20 07:30:00', '2026-09-18 16:20:00', FALSE),
    (5, 'Microplastics Survey',           'l3', '2026-09-05 09:00:00', '2026-09-23 11:05:00', FALSE);

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
    (5, 8, 'edit');

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
     NULL, NULL, FALSE,
     NULL, NULL, FALSE),
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
-- Activity log (only authenticated actions; profile_id NOT NULL)
-- ---------------------------------------------------------------------------

INSERT INTO `activity_log`
    (`profile_id`, `acted_at`, `entity_type`, `entity_id`, `activity_type`, `detail`)
VALUES
    (1, '2026-07-01 09:00:00', 'company', 'c1', 'add_lab',      'Added lab Molecular Biology Lab'),
    (1, '2026-07-01 09:30:00', 'company', 'c2', 'create',       'Registered company Helix Environmental Labs'),
    (3, '2026-08-10 09:15:00', 'project', '1',  'create',       'Created project Enzyme Kinetics Study'),
    (3, '2026-08-11 10:20:00', 'experiment', '1', 'create',     'Created experiment Amylase activity across pH gradients'),
    (3, '2026-08-25 09:00:00', 'experiment', '2', 'create',      'Created experiment Substrate concentration series'),
    (6, '2026-09-12 14:50:00', 'experiment', '1', 'update',      'Updated log text for experiment 1'),
    (3, '2026-09-14 18:05:00', 'experiment', '2', 'update',      'Completed log for experiment 2'),
    (5, '2026-09-10 11:00:00', 'experiment', '5', 'create',       'Created experiment Inter-lab reproducibility run'),
    (5, '2026-09-20 16:00:00', 'profile',  '5',  'delete',       'Profile anonymized per GDPR request'),
    (4, '2026-09-22 15:30:00', 'project',  '3',  'update',       'Renamed project Assay Validation'),
    (7, '2026-09-18 16:20:00', 'experiment', '6', 'update',      'Completed log for Well sampling round 1'),
    (7, '2026-09-23 11:05:00', 'experiment', '8', 'update',      'Updated log text for Beach sediment transects');

-- ---------------------------------------------------------------------------
-- Login log (nullable profile_id: unknown emails / pre-deletion failures)
-- ---------------------------------------------------------------------------

INSERT INTO `login_log`
    (`profile_id`, `email`, `login_at`, `ip_address`, `success`)
VALUES
    (3,   'carla.rossi@example.com',   '2026-09-22 14:05:00', '192.168.1.23',  TRUE),
    (4,   'david.kim@example.com',      '2026-09-23 11:45:00', '192.168.1.41',  TRUE),
    (6,   'eva.novak@example.com',      '2026-09-23 10:20:00', '192.168.1.58',  TRUE),
    (7,   'grace.liu@example.com',      '2026-09-22 08:55:00', '10.0.0.14',     TRUE),
    (5,   'deleted_5@example.com',      '2026-09-20 15:58:00', '192.168.1.72',  TRUE),   -- last login before anonymization
    (NULL, 'unknown@example.com',       '2026-09-23 03:12:00', '203.0.113.7',   FALSE),  -- unknown email
    (NULL, 'deleted_5@example.com',     '2026-09-23 03:15:00', '203.0.113.7',   FALSE),  -- deleted profile, anonymized email kept
    (NULL, 'alice.chen@example.com',    '2026-09-23 03:20:00', '203.0.113.7',   FALSE),  -- rate-limit demo: burst of 3 failures
    (NULL, 'alice.chen@example.com',    '2026-09-23 03:22:00', '203.0.113.7',   FALSE),  -- within 15 minutes
    (NULL, 'alice.chen@example.com',    '2026-09-23 03:24:00', '203.0.113.7',   FALSE);  -- within 15 minutes
