CREATE DATABASE protege;
GO
USE protege;
GO

CREATE TABLE users (
  userid INT IDENTITY PRIMARY KEY,
  name NVARCHAR(100),
  last_name NVARCHAR(100),
  email NVARCHAR(255) UNIQUE NOT NULL,
  password NVARCHAR(255) NOT NULL,
  token NVARCHAR(64)
);

CREATE TABLE people (
  id INT IDENTITY PRIMARY KEY,
  name NVARCHAR(100),
  address NVARCHAR(255),
  age INT,
  last_name NVARCHAR(100),
  last_name2 NVARCHAR(100),
  middle_name NVARCHAR(100),
  rut NVARCHAR(50)
);

CREATE TABLE assessments (
  id INT IDENTITY PRIMARY KEY,
  input NVARCHAR(MAX),
  result NVARCHAR(255),
  personid INT,
  userid INT,
  FOREIGN KEY (personid) REFERENCES people(id),
  FOREIGN KEY (userid) REFERENCES users(userid)
);
