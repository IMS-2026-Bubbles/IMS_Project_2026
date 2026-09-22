
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
  `Date_Updated` TIMESTAMP,
  `Project_Done` BOOLEAN,
  PRIMARY KEY (`Project_ID`),
  FOREIGN KEY (`Lab_Group_ID`)
      REFERENCES `Lab_Group`(`Lab_Group_ID`)
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
  `Date_Updated` TIMESTAMP,
  `Plan_Text` TEXT,
  `Plan_Updated` TIMESTAMP,
  `Plan_Done` BOOLEAN,
  `Log_Text` TEXT,
  `Log_Updated` TIMESTAMP,
  `Log_Done` BOOLEAN,
  `Result_Text` TEXT,
  `Result_Updated` TIMESTAMP,
  `Result_Done` BOOLEAN,
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

CREATE TABLE `User` (
  `User_ID` INT AUTO_INCREMENT, /* autoincrement only on integers, Tilda changed this */
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

CREATE TABLE `Company_Member` (
  `Company_ID` VARCHAR(10),
  `User_ID` INT AUTO_INCREMENT, /* autoincrement only on integers, Tilda changed this */
  `Role` ENUM('admin', 'member') NOT NULL,
  PRIMARY KEY (`Company_ID`, `User_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`),
  FOREIGN KEY (`Company_ID`)
      REFERENCES `Company`(`Company_ID`)
);

CREATE TABLE `Project_Member` (
  `Project_ID` VARCHAR(10),
  `User_ID` INT AUTO_INCREMENT, /* autoincrement only on integers, Tilda changed this */
  `Role` ENUM('owner', 'edit', 'read') NOT NULL,
  PRIMARY KEY (`Project_ID`, `User_ID`),
  FOREIGN KEY (`Project_ID`)
      REFERENCES `Project`(`Project_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`)
);

CREATE TABLE `Experiment_Member` (
  `Experiment_ID` VARCHAR(10),
  `User_ID` INT AUTO_INCREMENT, /* autoincrement only on integers, Tilda changed this */
  `Role` ENUM('edit', 'read') NOT NULL,
  PRIMARY KEY (`Experiment_ID`, `User_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`),
  FOREIGN KEY (`Experiment_ID`)
      REFERENCES `Proj_Experiment`(`Experiment_ID`)
);

CREATE TABLE `Lab_Group_Member` (
  `Lab_Group_ID` VARCHAR(10),
  `User_ID` INT AUTO_INCREMENT, /* autoincrement only on integers, Tilda changed this */
  `Role` ENUM('admin', 'member') NOT NULL,
  PRIMARY KEY (`Lab_Group_ID`, `User_ID`),
  FOREIGN KEY (`Lab_Group_ID`)
      REFERENCES `Lab_Group`(`Lab_Group_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`)
);

CREATE TABLE `Scriba_Member` (
  `User_ID` INT AUTO_INCREMENT, /* autoincrement only on integers, Tilda changed this */
  PRIMARY KEY (`User_ID`),
  FOREIGN KEY (`User_ID`)
      REFERENCES `User`(`User_ID`)
);


/* Tilda adding test users n*/ 
