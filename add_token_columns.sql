IF COL_LENGTH('users', 'token_hash') IS NULL
BEGIN
    ALTER TABLE users ADD token_hash NVARCHAR(64) UNIQUE;
END;

IF COL_LENGTH('users', 'token_expires_at') IS NULL
BEGIN
    ALTER TABLE users ADD token_expires_at DATETIME;
END;

IF COL_LENGTH('users', 'token_used') IS NULL
BEGIN
    ALTER TABLE users ADD token_used BIT DEFAULT 0;
END;

IF COL_LENGTH('users', 'token') IS NOT NULL
BEGIN
    ALTER TABLE users DROP COLUMN token;
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

