# PM Surya Ghar Platform

A comprehensive solar installation management platform.

## Project Structure

This project follows a professional dual-stack architecture:

- **/frontend**: React (Vite) + TypeScript frontend application.
- **/backend**:
  - **/laravel**: Laravel PHP API service (Primary backend).
  - **/node-service**: Placeholder for future Node.js microservices.

## Getting Started

### Prerequisites
- Node.js (v18+)
- PHP (v8.2+)
- Composer
- MySQL

### Frontend Setup
```bash
cd frontend
npm install
npm run dev
```

### Backend Setup (Laravel)
```bash
cd backend/laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Deployment

Deployment is handled via the centralized sync script:
`/Users/computergallery/Documents/pmsuryaghar/_agents/scripts/sync-to-server.sh`
