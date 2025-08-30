#!/bin/bash
set -e

/opt/mssql/bin/sqlservr &
PID=$!

# Wait for SQL Server to start accepting connections
echo "Waiting for SQL Server to start..."
until /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$SA_PASSWORD" -Q "SELECT 1" &> /dev/null; do
  sleep 1
done

# Always run initialization scripts to apply migrations
for f in /docker-entrypoint-initdb.d/*.sql; do
  [ -f "$f" ] || continue
  echo "Running $f"
  /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$SA_PASSWORD" -d master -i "$f"
done

wait $PID
