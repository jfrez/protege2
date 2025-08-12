#!/bin/bash
set -e

/opt/mssql/bin/sqlservr &
PID=$!

# Wait for SQL Server to start
sleep 20

# Run initialization scripts only if database does not exist
if ! /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$SA_PASSWORD" -Q "IF DB_ID('protege') IS NULL PRINT 1" -h -1 | grep -q 1; then
  echo "Database protege already exists; skipping initialization scripts."
else
  for f in /docker-entrypoint-initdb.d/*.sql; do
    echo "Running $f"
    /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$SA_PASSWORD" -d master -i "$f"
  done
fi

wait $PID
