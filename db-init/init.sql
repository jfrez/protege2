IF DB_ID(N'protege') IS NULL
BEGIN
    CREATE DATABASE protege;
END
GO

USE protege;
GO

IF OBJECT_ID(N'users', N'U') IS NULL
BEGIN
    CREATE TABLE users (
      userid INT IDENTITY PRIMARY KEY,
      name NVARCHAR(100),
      last_name NVARCHAR(100),
      email NVARCHAR(255) UNIQUE NOT NULL,
      password NVARCHAR(255) NOT NULL,
      token NVARCHAR(64)
    );
END

-- Ensure role column exists for admin/user distinction
IF COL_LENGTH('users', 'role') IS NULL
BEGIN
    ALTER TABLE users ADD role NVARCHAR(20) NOT NULL DEFAULT 'user';
END

-- Create default admin account if not present
IF NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@example.com')
BEGIN
    INSERT INTO users (name, last_name, email, password, role)
    VALUES ('Admin', 'User', 'admin@example.com', '$2y$12$pIRJVfkFCavvy/VmyqJXTuyN1vDkCwscRYDj5Mi0.7ueK/ebkpEve', 'admin');
END

IF OBJECT_ID(N'people', N'U') IS NULL
BEGIN
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
END

IF OBJECT_ID(N'assessments', N'U') IS NULL
BEGIN
    CREATE TABLE assessments (
      id INT IDENTITY PRIMARY KEY,
      input NVARCHAR(MAX),
      result NVARCHAR(255),
      personid INT,
      userid INT,
      FOREIGN KEY (personid) REFERENCES people(id),
      FOREIGN KEY (userid) REFERENCES users(userid)
    );
END
