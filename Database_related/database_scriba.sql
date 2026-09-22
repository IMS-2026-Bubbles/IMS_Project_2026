

/*database sql code start*/ 
/* parent table n*/ 
/* this is not the final porduct*/

CREATE TABLE Company (
    Company_ID VARCHAR(10) PRIMARY KEY /*AUTO_INCREMENT*/, /*till company memeber*/ 
    Comp_Name VARCHAR(100)
);

/*both parent and child table? */ 

CREATE TABLE Lab_Group (
    Lab_Group_ID VARCHAR(10) PRIMARY KEY /*AUTO_INCREMENT*/,/* LAB group memeber till ID*/ 
    Comapny_ID VARCHAR(10) FOREIGN KEY, /* company memeber längst upp och company ID) */
    Lab_Name VARCHAR(100)
);

CREATE TABLE Company_Member(
    Company_ID VARCHAR(10) PRIMARY KEY FOREIGN KEY /*AUTO_INCREMENT*/, /* linked to company (compny id) and fk company id inn lab group*/ 
    User_ID VARCHAR(10) PRIMARY KEY FOREIGN KEY,
    Role ENUM('admin', 'member') NOT NULL
);


CREATE TABLE Lab_Group_Member(
    Lab_Group_ID VARCHAR(10) PRIMARY KEY FOREIGN KEY /*AUTO_INCREMENT*/, /*lab group linked*/
    User_ID VARCHAR(10) PRIMARY KEY FOREIGN KEY, /* linked to both user and lab group*/
    Role ENUM('admin', 'member') NOT NULL
);


CREATE TABLE USER(
    User_ID VARCHAR(10) PRIMARY KEY /*AUTO_INCREMENT*/, /* To lab group memeber and company memebr */
    Email VARCHAR(255) NOT NULL UNIQUE,
    First_Name VARCHAR(50),
    Last_Name VARCHAR(50),
    Salt VARCHAR(255),
    
    Changes_saved INT,
    Last_Login TIMESTAMP,
    Streak INT
    
);


CREATE TABLE Proj_Tag(
    Project_ID VARCHAR(10) PRIMARY KEY FOREIGN KEY,
    Proj_Tag VARCHAR(10)
);


CREATE TABLE Project(
    Project_ID VARCHAR(10) PRIMARY KEY, 
    Project_Name VARCHAR(255) NOT NULL,
    Lab_Group_ID VARCHAR(10) NOT NULL FOREIGN KEY, /* FOREIGN KEY TO Lab_group*/
    Date_Created TIMESTAMP,
    Project_Done BOOLEAN,
);

CREATE TABLE Project_Member(
    Project_ID VARCHAR(10) PRIMARY KEY FOREIGN KEY, /* länkad till project tag */
    User_ID VARCHAR(10) PRIMARY KEY FOREIGN KEY,
    Role ENUM('owner', 'edit', 'read') NOT NULL
);

CREATE TABLE Exp_Tag(
    Experiment_ID VARCHAR(10) PRIMARY KEY FOREIGN KEY,
    Exp_Tag VARCHAR(10) PRIMARY KEY, 
);

CREATE TABLE Proj_Experiment(
    Experiment_ID VARCHAR(10) PRIMARY KEY,
    Experiment_Name VARCHAR(255) NOT NULL,
    Project_ID VARCHAR(10) FOREIGN KEY, /* till project*/
    Date_Created TIMESTAMP,
    Date_Updated TIMESTAMP,
    Plan_Text TEXT,
    Plan_Updated TIMESTAMP,
    Plan_Done BOOLEAN Log_Text TEXT,
    Log_Updated TIMESTAMP,
    Log_Done BOOLEAN,
    Result_Text TEXT,
    Result_Updated TIMESTAMP,
    Result_Done BOOLEAN
);







