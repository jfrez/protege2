#!/bin/bash
set -e

/opt/mssql/bin/sqlservr &
PID=$!

# Wait for SQL Server to start accepting connections
echo "Waiting for SQL Server to start..."
until /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$SA_PASSWORD" -Q "SELECT 1" &> /dev/null; do
  sleep 1
done

# Run initialization scripts only if database does not exist
if /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$SA_PASSWORD" -Q "IF DB_ID('protege') IS NULL PRINT 1" -h -1 | grep -q 1; then
  for f in /docker-entrypoint-initdb.d/*.sql; do
    [ -f "$f" ] || continue
    echo "Running $f"
    /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$SA_PASSWORD" -d master -i "$f"
  done
else
  echo "Database protege already exists; skipping initialization scripts."
fi

wait $PID
