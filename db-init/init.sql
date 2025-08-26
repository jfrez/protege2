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

IF OBJECT_ID(N'dbo.evaluacion', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.evaluacion (
      id INT IDENTITY PRIMARY KEY,
      nombre NVARCHAR(255),
      rut NVARCHAR(50),
      fecha_nacimiento DATE,
      edad INT,
      escolaridad NVARCHAR(255),
      region NVARCHAR(255),
      localidad NVARCHAR(255),
      zona NVARCHAR(255),
      sexo NVARCHAR(50),
      diversidad NVARCHAR(50),
      diversidad_cual NVARCHAR(255),
      nacionalidad NVARCHAR(255),
      pais_origen NVARCHAR(255),
      situacion_migratoria NVARCHAR(255),
      pueblo NVARCHAR(255),
      pueblo_cual NVARCHAR(255),
      convivencia NVARCHAR(255),
      maltrato NVARCHAR(255),
      otro_maltrato NVARCHAR(255),
      relacion_perpetrador NVARCHAR(255),
      otro_relacion NVARCHAR(255),
      fuente NVARCHAR(255),
      evaluador NVARCHAR(255),
      profesion NVARCHAR(255),
      centro NVARCHAR(255),
      fecha_evaluacion DATE,
      user_id INT,
      token NVARCHAR(64),
      login_method NVARCHAR(20),
      valoracion_global NVARCHAR(255),
      comentarios NVARCHAR(MAX),
      obs_caracterizacion NVARCHAR(MAX),
      obs_variables_extra NVARCHAR(MAX),
        CONSTRAINT FK_evaluacion_users FOREIGN KEY (user_id) REFERENCES dbo.users(userid)
    );
END

IF OBJECT_ID(N'dbo.factores_individuales', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.factores_individuales (
      id INT IDENTITY PRIMARY KEY,
      evaluacion_id INT NOT NULL,
      enfermedades_cronicas_discapacidad NVARCHAR(1),
      alteraciones_graves_comportamiento NVARCHAR(1),
      desvinculacion_ausentismo_escolar NVARCHAR(1),
      denuncias_ingresos_maltrato_previo NVARCHAR(1),
      terapia_nna NVARCHAR(1),
      CONSTRAINT FK_factores_individuales_evaluacion FOREIGN KEY (evaluacion_id) REFERENCES dbo.evaluacion(id)
    );
END
