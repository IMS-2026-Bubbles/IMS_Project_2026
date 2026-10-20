-- Mock example data for the Scriba schema.
-- Run AFTER database_schema.sql (same database: scriba_db).

-- Notes:
  -- All primary keys are INT AUTO_INCREMENT (including companies and labs).
  -- IDs are inserted explicitly here so the rows can reference each other;
  -- AUTO_INCREMENT continues after the highest inserted ID for new rows.
  -- Passwords are PLACEHOLDER bcrypt-format strings and cannot be used to
  -- log in. Register real users through the app instead.
  -- All seeded profiles have is_verified = TRUE so email verification does
  -- not block login once real hashes are in place.
  -- The dataset intentionally covers documented edge cases:
    -- profile 5 is GDPR-anonymized (is_deleted, kept memberships, still
    --    owns project 3 -> "orphan project", admin ownership-transfer case)
    -- profile 1 is in company 1 but in no lab (user in company without lab)
    -- profile 9 has a PENDING company membership (accepting an invitation)
    -- profile 13 has no company at all (user with no company)
    -- profile 8 has read-only access everywhere (incl. an access_denied entry)
    -- lab 2 has no members and no projects (empty lab)
    -- login_log contains failed logins and a rate-limit burst
  -- YOUR DEMO ACCOUNTS (registered through the app, then promoted manually):
    -- see the commented statements at the BOTTOM of this file.
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
    (2, 'Helix Environmental Labs'),
    (3, 'Nordwind Pharma');

INSERT INTO `labs` (`lab_id`, `company_id`, `name`) VALUES
    (1, 1, 'Molecular Biology Lab'),
    (2, 1, 'Analytical Chemistry Lab'),   -- no members, no projects: empty lab
    (3, 2, 'Water Quality Lab'),
    (4, 2, 'Soil & Sediment Lab'),
    (5, 3, 'Formulation Lab'),
    (6, 3, 'QC Stability Lab');

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
     TRUE, 128, @now - INTERVAL 26 HOUR, 12, TRUE, NULL, FALSE, FALSE),   -- company 1, but in NO lab
    (2, 'bob.martinez@example.com', 'Bob', 'Martinez',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 86,  @now - INTERVAL 25 HOUR, 7,  TRUE, NULL, FALSE, FALSE),   -- company 1 admin
    (3, 'carla.rossi@example.com', 'Carla', 'Rossi',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 210, @now - INTERVAL 48 HOUR, 21, TRUE, NULL, FALSE, FALSE),
    (4, 'david.kim@example.com', 'David', 'Kim',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 64,  @now - INTERVAL 24 HOUR, 3,  TRUE, NULL, FALSE, FALSE),   -- lab 1 admin
    (5, 'deleted_5@example.com', 'Deleted', 'Deleted',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKK',
     TRUE, 0,   @now - INTERVAL 100 HOUR, 0, TRUE, NULL, FALSE, TRUE),   -- anonymized (GDPR); streak and saved_changes reset to 0
    (6, 'eva.novak@example.com', 'Eva', 'Novak',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 142, @now - INTERVAL 27 HOUR, 15, TRUE, NULL, FALSE, FALSE),
    (7, 'grace.liu@example.com', 'Grace', 'Liu',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 38,  @now - INTERVAL 50 HOUR, 5,  TRUE, NULL, FALSE, FALSE),  -- lab 3 admin
    (8, 'henry.adams@example.com', 'Henry', 'Adams',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 9,   @now - INTERVAL 72 HOUR, 1,  TRUE, NULL, FALSE, FALSE),   -- read-only everywhere
    (9, 'ines.fournier@example.com', 'Ines', 'Fournier',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 0,   @now - INTERVAL 2 HOUR, 1,  TRUE, NULL, FALSE, FALSE),   -- PENDING invitation to company 3
    (10, 'jonas.weber@example.com', 'Jonas', 'Weber',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 97,  @now - INTERVAL 30 HOUR, 9,  TRUE, NULL, FALSE, FALSE),
    (11, 'katarina.berg@example.com', 'Katarina', 'Berg',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 175, @now - INTERVAL 20 HOUR, 18, TRUE, NULL, FALSE, FALSE),  -- lab 5 admin
    (12, 'liam.oconnor@example.com', 'Liam', "O'Connor",
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 51,  @now - INTERVAL 8 HOUR, 6,  TRUE, NULL, FALSE, FALSE),
    (13, 'mei.tanaka@example.com', 'Mei', 'Tanaka',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 22,  @now - INTERVAL 4 HOUR, 4,  TRUE, NULL, FALSE, FALSE),   -- NO company at all
    (14, 'noah.silva@example.com', 'Noah', 'Silva',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 73,  @now - INTERVAL 46 HOUR, 8,  TRUE, NULL, FALSE, FALSE),  -- lab 4 admin
    (15, 'priya.sharma@example.com', 'Priya', 'Sharma',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 188, @now - INTERVAL 12 HOUR, 17, TRUE, NULL, FALSE, FALSE), -- lab 6 admin
    (16, 'tomas.vlk@example.com', 'Tomas', 'Vlk',
     '$2y$10$MOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKMOCKM',
     TRUE, 34,  @now - INTERVAL 70 HOUR, 2,  TRUE, NULL, FALSE, FALSE);

-- ---------------------------------------------------------------------------
-- Company memberships
--   Profile 13 (Mei) is in NO company: "user with no company".
--   Profile 9 (Ines) is PENDING in company 3: "user accepting an invitation".
--   Profile 1 (Alice) is in company 1 but in no lab: "user in company without lab".
-- ---------------------------------------------------------------------------

INSERT INTO `company_members` (`company_id`, `profile_id`, `role`) VALUES
    (1, 1, 'member'),
    (1, 2, 'admin'),
    (1, 3, 'member'),
    (1, 4, 'member'),
    (1, 5, 'member'),   -- kept after anonymization
    (1, 6, 'member'),
    (2, 7, 'member'),
    (2, 8, 'member'),
    (2, 10, 'member'),
    (2, 14, 'admin'),
    (3, 9, 'pending'),  -- invitation not yet accepted (the only pending row)
    (3, 11, 'admin'),
    (3, 12, 'member'),
    (3, 15, 'member'),
    (3, 16, 'member');

-- ---------------------------------------------------------------------------
-- Lab memberships
--   Lab 2 (Analytical Chemistry) intentionally has no members or projects.
--   Alice (1) is in no lab; Mei (13) is in no company and no lab.
--   Ines (9) is pending in company 3 and not yet in any lab.
-- ---------------------------------------------------------------------------

INSERT INTO `lab_members` (`lab_id`, `profile_id`, `role`) VALUES
    (1, 3, 'member'),
    (1, 4, 'admin'),
    (1, 5, 'member'),   -- kept after anonymization
    (1, 6, 'member'),
    (3, 7, 'admin'),
    (3, 8, 'member'),
    (4, 10, 'member'),
    (4, 14, 'admin'),
    (5, 11, 'admin'),
    (5, 12, 'member'),
    (6, 15, 'admin'),
    (6, 16, 'member');

-- project_members and experiment_members are inserted further below,
-- after the projects/experiments they reference exist.

-- ---------------------------------------------------------------------------
-- Projects
--   Project 3 is the "orphan project": its only owner (profile 5) is
--   GDPR-anonymized, so a company/lab admin must transfer ownership.
-- ---------------------------------------------------------------------------

INSERT INTO `projects`
    (`project_id`, `name`, `lab_id`, `created_at`, `updated_at`, `is_done`)
VALUES
    (1,  'Enzyme Kinetics Study',          1, '2026-08-10 09:15:00', '2026-09-15 17:40:00', FALSE),
    (2,  'Protein Purification Protocol',  1, '2026-07-01 10:00:00', '2026-09-23 12:30:00', TRUE),   -- all experiments done
    (3,  'Assay Validation',               1, '2026-09-01 08:45:00', '2026-09-22 15:10:00', FALSE),  -- ORPHAN: owned by deleted profile 5
    (4,  'Groundwater Contaminant Screen', 3, '2026-08-20 07:30:00', '2026-09-18 16:20:00', FALSE),
    (5,  'Microplastics Survey',           3, '2026-09-05 09:00:00', '2026-09-23 11:05:00', FALSE),
    (6,  'Soil Metal Baseline Mapping',     4, '2026-07-15 08:00:00', '2026-09-12 14:35:00', FALSE),
    (7,  'Antibiotic Stability Screen',    6, '2026-06-02 09:30:00', '2026-08-30 16:00:00', TRUE),  -- all experiments done
    (8,  'Formulation Blend Trial A',      5, '2026-09-02 10:00:00', '2026-09-21 13:25:00', FALSE),
    (9,  'Shelf-Life Accelerated Study',   6, '2026-09-08 08:20:00', '2026-09-24 10:15:00', FALSE),
    (10, 'PFAS Screening Pilot',           3, '2026-08-05 09:10:00', '2026-09-01 15:45:00', TRUE);  -- all experiments done

INSERT INTO `project_tags` (`project_id`, `tag`) VALUES
    (1, 'kinetics'),
    (1, 'enzyme'),
    (2, 'chromatography'),
    (2, 'protocol'),
    (3, 'validation'),
    (3, 'elisa'),
    (4, 'fieldwork'),
    (4, 'groundwater'),
    (5, 'microplastics'),
    (5, 'survey'),
    (6, 'fieldwork'),
    (6, 'metals'),
    (7, 'stability'),
    (7, 'antibiotics'),
    (8, 'formulation'),
    (9, 'stability'),
    (10, 'pfas'),
    (10, 'screening');

INSERT INTO `project_members` (`project_id`, `profile_id`, `role`) VALUES
    (1,  3, 'owner'),
    (1,  4, 'read'),
    (1,  6, 'edit'),
    (2,  3, 'owner'),
    (2,  6, 'edit'),
    (3,  5, 'owner'),   -- deleted profile still owns project 3 (admin must transfer)
    (3,  4, 'edit'),
    (4,  7, 'owner'),
    (4,  8, 'read'),
    (5,  7, 'owner'),
    (5,  8, 'read'),
    (6,  10, 'owner'),
    (6,  14, 'edit'),
    (7,  15, 'owner'),
    (7,  16, 'edit'),
    (8,  11, 'owner'),
    (8,  12, 'edit'),
    (9,  12, 'owner'),
    (9,  15, 'edit'),
    (9,  16, 'read'),
    (10, 14, 'owner'),
    (10, 10, 'edit');

-- ---------------------------------------------------------------------------
-- Experiments
-- (updated_at / is_done are generated columns -- not inserted)
-- Experiments in done projects (2, 7, 10) are all done themselves.
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
     NULL, NULL, FALSE),
    (9, 'Reference site sampling', 5, '2026-09-15 07:00:00',
     'Collect sediment at the unpolluted reference site for background correction.',
     '2026-09-15 07:05:00', TRUE,
     'Reference samples collected and stored at 4C pending FTIR analysis.',
     '2026-09-24 09:40:00', FALSE,
     NULL, NULL, FALSE),
    (10, 'Transect grid metal assay', 6, '2026-07-16 08:30:00',
     '20-site grid, 0-20 cm cores, aqua-regia digestion, ICP-OES for Cd, Pb, Zn, Cu.',
     '2026-07-16 08:35:00', TRUE,
     'All 20 sites digested in two batches; ICP calibration verified with CRM.',
     '2026-08-28 15:20:00', TRUE,
     'Elevated Pb at sites 7-9 near old road; otherwise near background.',
     '2026-09-12 14:35:00', TRUE),
    (11, 'Seasonal resample sites 7-9', 6, '2026-09-14 09:00:00',
     'Revisit elevated-Pb sites after road remediation; same digestion and ICP workflow.',
     '2026-09-14 09:05:00', TRUE,
     'Sampling completed; digestion scheduled for next week.',
     '2026-09-22 16:10:00', FALSE,
     NULL, NULL, FALSE),
    (12, 'Amoxicillin solution 25C stress', 7, '2026-06-03 10:00:00',
     'Store reconstituted solution at 25C; assay content by HPLC at days 0, 3, 7, 14.',
     '2026-06-03 10:05:00', TRUE,
     'Day 0-7 assays complete. Assay decline linear so far, ~2.1%/day.',
     '2026-06-17 14:00:00', TRUE,
     'Below 90% label claim at day 14. Solution stable under refrigeration only.',
     '2026-06-25 16:00:00', TRUE),
    (13, 'Amoxicillin tablet blister 40C/75%RH', 7, '2026-06-05 09:00:00',
     'Accelerated stability per ICH on blister-packed tablets; 6-month pull schedule.',
     '2026-06-05 09:05:00', TRUE,
     'Monthly pulls assayed through month 2; no significant degradation.',
     '2026-08-10 11:30:00', TRUE,
     'Tablets pass all specs at month 3; study closed for this screening round.',
     '2026-08-30 16:00:00', TRUE),
    (14, 'Blend ratio 60:40 vs 70:30', 8, '2026-09-03 10:30:00',
     'Compare two blend ratios for tablet uniformity and dissolution profile.',
     '2026-09-03 10:35:00', TRUE,
     'Both blends compressed; 70:30 shows tighter hardness distribution.',
     '2026-09-18 15:00:00', FALSE,
     NULL, NULL, FALSE),
    (15, 'Dissolution f2 comparison', 8, '2026-09-19 09:00:00',
     'Compute f2 similarity of the two blends against the reference dissolution profile.',
     '2026-09-19 09:05:00', TRUE,
     'Dissolution runs n=6 per blend complete; data cleaned and ready for analysis.',
     '2026-09-21 13:25:00', TRUE,
     NULL, NULL, FALSE),
    (16, 'Accelerated 40C/75%RH pull 1', 9, '2026-09-09 08:25:00',
     'First monthly pull of the accelerated shelf-life study; assay, water content, appearance.',
     '2026-09-09 08:30:00', TRUE,
     'Pull 1 assays complete; all results within specification.',
     '2026-09-24 10:15:00', TRUE,
     NULL, NULL, FALSE),
    (17, 'Photostability screen', 9, '2026-09-16 08:00:00',
     'ICH Q1B option 2 exposure; compare assay before and after light exposure.',
     '2026-09-16 08:05:00', TRUE,
     'Samples in light cabinet; exposure 1.2 million lux-hours in progress.',
     '2026-09-23 17:05:00', FALSE,
     NULL, NULL, FALSE),
    (18, 'SPE cartridge method validation', 10, '2026-08-06 09:00:00',
     'Validate solid-phase extraction for 6 PFAS analytes; recoveries 70-130%, RSD < 20%.',
     '2026-08-06 09:05:00', TRUE,
     'Spike recoveries 82-116% across all analytes; RSD 4-11%.',
     '2026-08-25 14:20:00', TRUE,
     'Method accepted; low-level calibration verified down to 2 ng/L.',
     '2026-09-01 15:45:00', TRUE),
    (19, 'Source water survey', 10, '2026-08-12 08:30:00',
     'Screen 8 source-water samples with the validated SPE-LC/MS/MS method.',
     '2026-08-12 08:35:00', TRUE,
     'All 8 samples extracted and run in duplicate; one sample re-run for carryover check.',
     '2026-08-29 16:40:00', TRUE,
     'PFOS detected above advisory in samples 3 and 6; results reported to client.',
     '2026-09-01 15:45:00', TRUE),
    (20, 'Enzyme batch comparison', 1, '2026-09-20 10:00:00',
     'Compare activity of enzyme lots A and B under the pH 6.5 peak condition.',
     '2026-09-20 10:05:00', TRUE,
     NULL, NULL, FALSE,
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
    (8, 'sediment'),
    (9, 'reference'),
    (10, 'icp-oes'),
    (11, 'icp-oes'),
    (12, 'hplc'),
    (13, 'ich'),
    (14, 'dissolution'),
    (15, 'f2'),
    (16, 'stability'),
    (17, 'photostability'),
    (18, 'lc-ms'),
    (19, 'lc-ms');

INSERT INTO `experiment_members` (`experiment_id`, `profile_id`, `role`) VALUES
    (1,  4, 'read'),
    (1,  6, 'edit'),
    (2,  3, 'edit'),
    (5,  4, 'edit'),
    (6,  7, 'edit'),
    (6,  8, 'read'),
    (8,  7, 'edit'),
    (9,  8, 'read'),
    (10, 10, 'edit'),
    (11, 14, 'edit'),
    (12, 16, 'edit'),
    (13, 15, 'edit'),
    (14, 12, 'edit'),
    (15, 12, 'edit'),
    (16, 16, 'edit'),
    (17, 15, 'edit'),
    (18, 10, 'edit'),
    (19, 14, 'read'),
    (20, 6, 'edit');

-- ---------------------------------------------------------------------------
-- Activity log (only authenticated actions; profile_id NOT NULL in practice)
-- entity_id is INT and holds the numeric ID of the entity.
-- Timestamps are relative to @now so they stay inside the 90-day retention.
-- ---------------------------------------------------------------------------

INSERT INTO `activity_log`
    (`profile_id`, `acted_at`, `entity_type`, `entity_id`, `activity_type`, `detail`)
VALUES
    (2,  @now - INTERVAL 85 DAY,  'company',    1,  'add_company',   'Registered company Asteria Biotech'),
    (7,  @now - INTERVAL 84 DAY,  'company',    2,  'add_company',   'Registered company Helix Environmental Labs'),
    (11, @now - INTERVAL 83 DAY,  'company',    3,  'add_company',   'Registered company Nordwind Pharma'),
    (2,  @now - INTERVAL 80 DAY,  'company',    1,  'add_lab',       'Added lab Molecular Biology Lab'),
    (14, @now - INTERVAL 78 DAY,  'company',    2,  'add_lab',       'Added lab Soil & Sediment Lab'),
    (15, @now - INTERVAL 76 DAY,  'company',    3,  'add_lab',       'Added lab QC Stability Lab'),
    (3,  @now - INTERVAL 59 DAY,  'project',    1,  'create',        'Created project Enzyme Kinetics Study'),
    (3,  @now - INTERVAL 58 DAY,  'experiment', 1,  'create',        'Created experiment Amylase activity across pH gradients'),
    (3,  @now - INTERVAL 44 DAY,  'experiment', 2,  'create',        'Created experiment Substrate concentration series'),
    (3,  @now - INTERVAL 42 DAY,  'project',    2,  'create',        'Created project Protein Purification Protocol'),
    (5,  @now - INTERVAL 40 DAY,  'project',    3,  'create',        'Created project Assay Validation'),
    (5,  @now - INTERVAL 35 DAY,  'experiment', 5,  'create',        'Created experiment Inter-lab reproducibility run'),
    (14, @now - INTERVAL 30 DAY,  'project',    10, 'create',        'Created project PFAS Screening Pilot'),
    (10, @now - INTERVAL 28 DAY,  'project',    6,  'create',        'Created project Soil Metal Baseline Mapping'),
    (6,  @now - INTERVAL 26 DAY,  'experiment', 1,  'update',        'Updated log text for experiment 1'),
    (3,  @now - INTERVAL 24 DAY,  'experiment', 2,  'update',        'Completed log for experiment 2'),
    (15, @now - INTERVAL 24 DAY,  'project',    7,  'create',        'Created project Antibiotic Stability Screen'),
    (11, @now - INTERVAL 22 DAY,  'project',    8,  'create',        'Created project Formulation Blend Trial A'),
    (4,  @now - INTERVAL 16 DAY,  'project',    3,  'update',        'Renamed project Assay Validation'),
    (7,  @now - INTERVAL 20 DAY,  'experiment', 6,  'update',        'Completed log for Well sampling round 1'),
    (7,  @now - INTERVAL 15 DAY,  'experiment', 8,  'update',        'Updated log text for Beach sediment transects'),
    (11, @now - INTERVAL 12 DAY,  'company',    3,  'add_member',    'Invitation sent to ines.fournier@example.com (pending)'),
    (12, @now - INTERVAL 6 DAY,   'experiment', 14, 'update',        'Updated log text for Blend ratio 60:40 vs 70:30'),
    (8,  @now - INTERVAL 5 DAY,   'experiment', 1,  'access_denied', 'Read-only member attempted to edit experiment 1'),
    (16, @now - INTERVAL 4 DAY,   'experiment', 16, 'update',        'Completed log for Accelerated 40C/75%RH pull 1'),
    (5,  @now - INTERVAL 99 HOUR, 'profile',    5,  'delete',        'Profile anonymized per GDPR request');

-- ---------------------------------------------------------------------------
-- Login log (nullable profile_id: unknown emails / pre-deletion failures)
-- The burst of failures for alice.chen is within the last 15 minutes after
-- loading, so the lockout query in the schema returns 3.
-- ---------------------------------------------------------------------------

INSERT INTO `login_log`
    (`profile_id`, `email`, `login_at`, `ip_address`, `success`, `detail`)
VALUES
    (3,  'carla.rossi@example.com',  @now - INTERVAL 48 HOUR,  '192.168.1.23', TRUE,  NULL),
    (4,  'david.kim@example.com',    @now - INTERVAL 24 HOUR,  '192.168.1.41', TRUE,  NULL),
    (6,  'eva.novak@example.com',    @now - INTERVAL 27 HOUR,  '192.168.1.58', TRUE,  NULL),
    (7,  'grace.liu@example.com',    @now - INTERVAL 50 HOUR,  '10.0.0.14',    TRUE,  NULL),
    (10, 'jonas.weber@example.com',  @now - INTERVAL 30 HOUR,  '10.0.0.22',    TRUE,  NULL),
    (11, 'katarina.berg@example.com', @now - INTERVAL 20 HOUR, '10.0.1.5',    TRUE,  NULL),
    (12, 'liam.oconnor@example.com', @now - INTERVAL 8 HOUR,   '10.0.1.9',     TRUE,  NULL),
    (13, 'mei.tanaka@example.com',   @now - INTERVAL 4 HOUR,   '203.0.113.44', TRUE, NULL),
    (15, 'priya.sharma@example.com', @now - INTERVAL 12 HOUR,  '10.0.1.30',    TRUE,  NULL),
    (1,  'alice.chen@example.com',   @now - INTERVAL 26 HOUR,  '192.168.1.10', TRUE,  NULL),
    (5,  'deleted_5@example.com',    @now - INTERVAL 100 HOUR, '192.168.1.72', TRUE,  'Last login before anonymization'),
    (NULL, 'unknown@example.com',      @now - INTERVAL 5 HOUR,    '203.0.113.7', FALSE, 'Email not found'),
    (NULL, 'deleted_5@example.com',    @now - INTERVAL 295 MINUTE, '203.0.113.7', FALSE, 'Profile deleted; anonymized email kept'),
    (NULL, 'alice.chen@example.com',   @now - INTERVAL 10 MINUTE, '203.0.113.7', FALSE, 'Incorrect password'),   -- rate-limit demo: burst of 3 failures
    (NULL, 'alice.chen@example.com',   @now - INTERVAL 8 MINUTE,  '203.0.113.7', FALSE, 'Incorrect password'),   -- within 15 minutes
    (NULL, 'alice.chen@example.com',   @now - INTERVAL 6 MINUTE,  '203.0.113.7', FALSE, 'Incorrect password');  -- within 15 minutes

-- ---------------------------------------------------------------------------
-- YOUR DEMO ACCOUNTS (run these AFTER registering through the app)
-- Replace the placeholder emails with the ones you registered with.
-- ---------------------------------------------------------------------------

-- Scriba admin:
-- UPDATE `profiles`
-- SET `is_scriba_admin` = TRUE
-- WHERE `email` = 'rasmus.hammar@gmx.com';

-- Company admin & active user (company 1 = Asteria Biotech; pick another
-- company_id if you prefer):
-- INSERT INTO `company_members` (`company_id`, `profile_id`, `role`)
-- SELECT 1, `profile_id`, 'admin'
-- FROM `profiles`
-- WHERE `email` = 'rasmus.hammar.1670@student.uu.se';
--
-- Optionally also add the company-admin account to lab 1 so it can see lab
-- projects directly:
-- INSERT INTO `lab_members` (`lab_id`, `profile_id`, `role`)
-- SELECT 1, `profile_id`, 'admin'
-- FROM `profiles`
-- WHERE `email` = 'rasmus.hammar.1670@student.uu.se';
