APP :
github.com/ArgaPBO/be-tokoroti
github.com/ArgaPBO/fe-tokoroti

tambahkan pada env backend
APP_URL=http://127.0.0.1:8080
FRONTEND_URL=http://127.0.0.1:8000
SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8000

tambahkan pada env frontend
API_URL=http://127.0.0.1:8080/api

backend 'php artisan serve --port=8080'
frontend 'yarn dev' 'php artisan serve --port=8000' (2 terminal)