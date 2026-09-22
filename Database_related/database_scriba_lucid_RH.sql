CREATE TABLE `Company` (
  `Company_ID` VARCHAR(10),
  `Comp_Name` VARCHAR(100),
  PRIMARY KEY (`Company_ID`)
);

CREATE TABLE `Lab_Group` (
  `Lab_Group_ID` VARCHAR(10),
  `Company_ID` VARCHAR(10),
  `Lab_Name` VARCHAR(100),
  PRIMARY KEY (`Lab_Group_ID`),
  FOREIGN KEY (`Company_ID`)
      REFERENCES `Company`(`Company_ID`)
);

CREATE TABLE `Project` (
  `Project_ID` VARCHAR(10),
  `Project_Name` VARCHAR(255) NOT NULL,
  `Lab_Group_ID` VARCHAR(10) NOT NULL,
  `Date_Created` TIMESTAMP,
  `Date_Updated` TIMESTAMP, -- Make this based on the most recent update time of any experiment in the project? Would have to be a separate VIEW table in that case
  `Project_Done` BOOLEAN,
  PRIMARY KEY (`Project_ID`),
  FOREIGN KEY (`Lab_Group_ID`)
      REFERENCES `Lab_Group`(`Lab_Group_ID`)
);

CREATE TABLE `User` (
  `User_ID` VARCHAR(10),
  `Email` VARCHAR(255) NOT NULL UNIQUE,
  `First_Name` VARCHAR(50),
  `Last_Name` VARCHAR(50),
  `Salt` BINARY(16),
  `Password` VARCHAR(255),
  `Changes_Saved` INT,
  `Last_Login` TIMESTAMP,
  `Streak` INT,
  PRIMARY KEY (`User_ID`)
);

CREATE TABLE `Project_Member` (
  `Project_ID` VARCHAR(10),
  `User_ID` VARCHAR(10),
  `Role` ENUM('owner', 'edit', 'read') NOT NULL,
  PRIMARY KEY (`Project_ID`, `User_ID`),
  FOREIGN KEY (`Project_ID`)
      REFERENCES `Project`(`Project_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`)
);

CREATE TABLE `Proj_Tag` (
  `Project_ID` VARCHAR(10),
  `Proj_Tag` VARCHAR(10),
  PRIMARY KEY (`Project_ID`, `Proj_Tag`),
  FOREIGN KEY (`Project_ID`)
      REFERENCES `Project`(`Project_ID`)
);

CREATE TABLE `Proj_Experiment` (
  `Experiment_ID` VARCHAR(10),
  `Experiment_Name` VARCHAR(255) NOT NULL,
  `Project_ID` VARCHAR(10),
  `Date_Created` TIMESTAMP,
  `Plan_Text` TEXT,
  `Plan_Updated` TIMESTAMP,
  `Plan_Done` BOOLEAN,
  `Log_Text` TEXT,
  `Log_Updated` TIMESTAMP,
  `Log_Done` BOOLEAN,
  `Result_Text` TEXT,
  `Result_Updated` TIMESTAMP,
  `Result_Done` BOOLEAN,
  `Date_Updated` TIMESTAMP -- GENERATED ALWAYS AS = automagic, generated column that shows the most recent update time for the experiment
    GENERATED ALWAYS AS (
      GREATEST(
        COALESCE(`Plan_Updated`, '1970-01-01 00:00:00'),
        COALESCE(`Log_Updated`, '1970-01-01 00:00:00'),
        COALESCE(`Result_Updated`, '1970-01-01 00:00:00')
      )
    ) STORED, -- Stored vs Virtual: Stored is faster for queries but takes more space.
                -- Stored is physically stored in the table, 
                -- Virtual is calculated on the fly.
  `Experiment_Done` BOOLEAN -- GENERATED ALWAYS AS = automagic, generated column that indicates whether the experiment is done based on the status of plan, log, and result
    GENERATED ALWAYS AS (
      `Plan_Done` AND `Log_Done` AND `Result_Done`
    ) STORED,
  PRIMARY KEY (`Experiment_ID`),
  FOREIGN KEY (`Project_ID`)
      REFERENCES `Project`(`Project_ID`)
);

CREATE TABLE `Exp_Tag` (
  `Experiment_ID` VARCHAR(10),
  `Exp_Tag` VARCHAR(10),
  PRIMARY KEY (`Experiment_ID`, `Exp_Tag`),
  FOREIGN KEY (`Experiment_ID`)
      REFERENCES `Proj_Experiment`(`Experiment_ID`)
);

CREATE TABLE `Company_Member` (
  `Company_ID` VARCHAR(10),
  `User_ID` VARCHAR(10),
  `Role` ENUM('admin', 'member') NOT NULL,
  PRIMARY KEY (`Company_ID`, `User_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`),
  FOREIGN KEY (`Company_ID`)
      REFERENCES `Company`(`Company_ID`)
);

CREATE TABLE `Experiment_Member` (
  `Experiment_ID` VARCHAR(10),
  `User_ID` VARCHAR(10),
  `Role` ENUM('edit', 'read') NOT NULL,
  PRIMARY KEY (`Experiment_ID`, `User_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`),
  FOREIGN KEY (`Experiment_ID`)
      REFERENCES `Proj_Experiment`(`Experiment_ID`)
);

CREATE TABLE `Lab_Group_Member` (
  `Lab_Group_ID` VARCHAR(10),
  `User_ID` VARCHAR(10),
  `Role` ENUM('admin', 'member') NOT NULL,
  PRIMARY KEY (`Lab_Group_ID`, `User_ID`),
  FOREIGN KEY (`Lab_Group_ID`)
      REFERENCES `Lab_Group`(`Lab_Group_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`)
);

CREATE TABLE `Scriba_Member` (
  `User_ID` VARCHAR(10),
  PRIMARY KEY (`User_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`)
);

CREATE VIEW `Scriba_Points` AS -- Table = Scriba_Points, Columns = User_ID, Scriba_Points
SELECT 
    `User_ID`,
    COALESCE(`Changes_Saved`, 0)
      + COALESCE(`Streak`, 0)
      + 100 * COALESCE(`done_projects`.`Project_Done_Count`, 0) 
      + 10 * COALESCE(`done_experiments`.`Experiment_Done_Count`, 0)
      AS `Scriba_Points`
FROM `User`
LEFT JOIN ( -- Count the number of completed projects for each user who is an owner
  SELECT `Project_Member`.`User_ID`, COUNT(*) AS `Project_Done_Count`
  FROM `Project_Member`
  JOIN `Project` 
    ON `Project_Member`.`Project_ID` = `Project`.`Project_ID`
  WHERE `Project_Member`.`Role` = 'owner'
    AND `Project`.`Project_Done` = TRUE
  GROUP BY `Project_Member`.`User_ID`
) `done_projects` -- name of this new subquery table 
  ON `User`.`User_ID` = `done_projects`.`User_ID`
LEFT JOIN ( -- Count the number of completed experiments for each user who is an owner
  SELECT `Project_Member`.`User_ID`, COUNT(*) AS `Experiment_Done_Count`
  FROM `Project_Member`
  JOIN `Proj_Experiment` 
    ON `Project_Member`.`Project_ID` = `Proj_Experiment`.`Project_ID`
  WHERE `Project_Member`.`Role` = 'owner'
    AND `Proj_Experiment`.`Experiment_Done` = TRUE
  GROUP BY `Project_Member`.`User_ID`
) `done_experiments` -- name of this new subquery table
  ON `User`.`User_ID` = `done_experiments`.`User_ID`;
