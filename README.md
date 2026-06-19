# LOGYMEX API

API REST para la gestión de servicios, operadores, bitácoras y evidencias del sistema LOGYMEX.

---

## Tecnologías

- PHP 8.3
- Laravel 11
- MySQL
- Laravel Sanctum

---

## Instalación

### Clonar repositorio

```bash
git clone https://github.com/USUARIO/logymex-api.git

cd logymex-api
```

### Instalar dependencias

```bash
composer install
```

### Instalar dependencias frontend

```bash
npm install
```

### Copiar archivo de entorno

```bash
cp .env.example .env
```

### Generar llave

```bash
php artisan key:generate
```

### Ejecutar migraciones

```bash
php artisan migrate
```

### Ejecutar seeders

```bash
php artisan db:seed
```

### Iniciar servidor

```bash
php artisan serve
```

---

## Variables importantes

```env (yo le puse usuario y contraseña a la BD, pero esta es opcional) 
APP_NAME=LOGYMEX
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

---

## Autenticación

El sistema utiliza Laravel Sanctum.

Se debe obtener un token mediante:

```http
POST /api/login
```

Y enviarlo:

```http
Authorization: Bearer TOKEN
```

---

## Endpoints principales

### Login

```http
POST /api/login
```

### Obtener servicios

```http
GET /api/services
```

### Registrar bitácora

```http
POST /api/logs
```

### Obtener operadores

```http
GET /api/operators
```
