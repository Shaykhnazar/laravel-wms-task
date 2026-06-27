#!/bin/sh
set -e

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ -z "$APP_KEY" ] || ! echo "$APP_KEY" | grep -q 'base64:'; then
  if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
  fi
fi

echo "Waiting for MySQL..."
attempt=0
max_attempts=60
while [ "$attempt" -lt "$max_attempts" ]; do
  if php -r "
    try {
        new PDO(
            'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'),
            [PDO::ATTR_TIMEOUT => 2]
        );
        exit(0);
    } catch (Throwable \$e) {
        exit(1);
    }
  " 2>/dev/null; then
    echo "MySQL is ready."
    break
  fi
  attempt=$((attempt + 1))
  sleep 2
done

if [ "$attempt" -ge "$max_attempts" ]; then
  echo "MySQL did not become ready in time."
  exit 1
fi

php artisan migrate --force

if php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! Illuminate\Support\Facades\Schema::hasTable('providers')) {
    exit(1);
}
exit(App\Models\Provider::query()->exists() ? 0 : 1);
" 2>/dev/null; then
  echo "Database already seeded, skipping."
else
  echo "Seeding database (first run)..."
  php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port=8000
