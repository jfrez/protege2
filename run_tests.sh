#!/bin/bash
set -euo pipefail

# Run PHP syntax check for all PHP files
php_files=$(find . -name '*.php')
if [ -n "$php_files" ]; then
  echo "Running PHP syntax checks..."
  for file in $php_files; do
    php -l "$file" >/tmp/php_lint.log && echo "OK - $file" || { cat /tmp/php_lint.log; exit 1; }
  done
else
  echo "No PHP files to lint."
fi

# Run Python compile checks for all Python files
python_files=$(find . -name '*.py')
if [ -n "$python_files" ]; then
  echo "Running Python compile checks..."
  python3 -m py_compile $python_files
else
  echo "No Python files to check."
fi

echo "All checks completed."
