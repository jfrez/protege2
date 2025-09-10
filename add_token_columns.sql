IF COL_LENGTH('users', 'token') IS NULL
BEGIN
    ALTER TABLE users ADD token NVARCHAR(64) UNIQUE;
END;

IF COL_LENGTH('users', 'must_change_password') IS NULL
BEGIN
    ALTER TABLE users ADD must_change_password BIT DEFAULT 1;
END;

IF COL_LENGTH('evaluacion', 'token') IS NULL
BEGIN
    ALTER TABLE evaluacion ADD token NVARCHAR(64);
END;

IF COL_LENGTH('evaluacion', 'login_method') IS NULL
BEGIN
    ALTER TABLE evaluacion ADD login_method NVARCHAR(20);
END;

IF COL_LENGTH('evaluacion', 'cod_nino') IS NULL
BEGIN
    ALTER TABLE evaluacion ADD cod_nino NVARCHAR(50);
END;

