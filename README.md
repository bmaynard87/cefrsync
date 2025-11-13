# CefrSync

A comprehensive language learning companion application built with Laravel, Inertia.js, and Vue.js. CefrSync provides AI-powered conversations, progress tracking, and personalized feedback to help users improve their language skills.

## Overview

CefrSync is a full-stack web application that connects learners with AI tutors for language practice. It integrates with the LangGPT microservice for advanced language processing and leverages OpenAI for intelligent conversations.

## Features

- 🗣️ **AI-Powered Conversations**: Chat with an AI tutor in your target language
- 📊 **Progress Tracking**: Track conversations and learning progress over time
- 🎯 **CEFR Level Support**: Tailored content for A1 through C2 proficiency levels
- 💡 **Insights & Feedback**: Get real-time insights on your language usage
- 👤 **User Authentication**: Secure registration and login with Laravel Breeze
- 🎨 **Modern UI**: Built with Tailwind CSS and shadcn/ui components
- 📱 **Responsive Design**: Works seamlessly on desktop and mobile devices

## Tech Stack

### Backend
- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **Authentication**: Laravel Breeze (Inertia stack)
- **Queue**: Redis/Database
- **Testing**: Pest PHP

### Frontend
- **Framework**: Vue.js 3 with TypeScript
- **SSR**: Inertia.js
- **UI Components**: shadcn/ui
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **Testing**: Vitest + Vue Test Utils

### External Services
- **LangGPT**: FastAPI microservice for language processing
- **OpenAI**: GPT-based conversation engine

## Project Structure

```
cefrsync/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Application controllers
│   │   ├── Middleware/       # Custom middleware
│   │   └── Requests/         # Form requests
│   ├── Jobs/
│   │   └── AnalyzeRecentMessages.php  # Queue jobs
│   ├── Models/               # Eloquent models
│   ├── Services/
│   │   └── LangGptService.php  # LangGPT integration
│   └── Providers/            # Service providers
├── resources/
│   ├── js/
│   │   ├── components/       # Vue components
│   │   │   ├── Chat/         # Chat-related components
│   │   │   ├── Insights/     # Insight components
│   │   │   └── ui/           # shadcn/ui components
│   │   ├── composables/      # Vue composables
│   │   ├── Pages/            # Inertia pages
│   │   └── types/            # TypeScript definitions
│   ├── css/
│   │   └── app.css           # Global styles
│   └── views/                # Blade templates
├── routes/
│   ├── web.php               # Web routes
│   ├── auth.php              # Auth routes
│   └── settings.php          # Settings routes
├── tests/
│   ├── Feature/              # Feature tests (PHP)
│   ├── Unit/                 # Unit tests (PHP)
│   └── components/           # Component tests (Vue/TypeScript)
├── config/                   # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   ├── seeders/              # Database seeders
│   └── factories/            # Model factories
└── public/                   # Public assets
```

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- NPM or Yarn
- MySQL/PostgreSQL
- Redis (optional, for queues)
- Docker & Docker Compose (recommended)

### Environment Variables

Copy `.env.example` to `.env` and configure:

```env
APP_NAME=CefrSync
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cefrsync
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=your_openai_api_key_here

LANGGPT_BASE_URL=http://host.docker.internal:8000
LANGGPT_API_KEY=your_langgpt_api_key_here
```

### Installation

#### Using Composer Setup Script

The fastest way to get started:

```bash
composer setup
```

This runs:
- `composer install`
- `php artisan key:generate`
- `php artisan migrate`
- `npm install`
- `npm run build`

#### Manual Installation

1. **Install PHP dependencies:**

```bash
composer install
```

2. **Install JavaScript dependencies:**

```bash
npm install
```

3. **Generate application key:**

```bash
php artisan key:generate
```

4. **Run migrations:**

```bash
php artisan migrate
```

5. **Build frontend assets:**

```bash
npm run build
```

### Running the Application

#### Development Mode (Recommended)

Start all services with a single command:

```bash
composer dev
```

This starts:
- Laravel development server (`:8000`)
- Queue worker
- Log viewer
- Vite dev server (hot reload)

#### Individual Services

**Laravel development server:**

```bash
php artisan serve
```

**Vite dev server (hot reload):**

```bash
npm run dev
```

**Queue worker:**

```bash
php artisan queue:work
```

#### Production Build

```bash
npm run build
```

### Docker Deployment

```bash
docker-compose up -d
```

## Testing

### PHP Tests (Pest)

Run all PHP tests:

```bash
php artisan test
```

Or using the artisan shorthand:

```bash
art test
```

Run specific test file:

```bash
php artisan test --filter=ChatControllerTest
```

### Frontend Tests (Vitest)

Run all component tests:

```bash
npm test
```

Run tests in watch mode:

```bash
npm test -- --watch
```

Run with coverage:

```bash
npm test -- --coverage
```

### End-to-End Tests (Playwright)

Run E2E tests (requires Docker stack and Vite dev server running):

```bash
npm run test:e2e
```

Run with UI mode (recommended for development):

```bash
npm run test:e2e:ui
```

Run in headed mode (see the browser):

```bash
npm run test:e2e:headed
```

Debug specific test:

```bash
npm run test:e2e:debug
```

**Prerequisites for E2E tests:**
1. Start Docker stack: `./vendor/bin/sail up -d`
2. Start Vite dev server: `npm run dev`
3. Ensure database is seeded: `./vendor/bin/sail artisan migrate:fresh --seed`

See `e2e/README.md` for more details.

### Test Structure

- **PHP Tests** (`tests/Feature/`, `tests/Unit/`): Backend logic, API endpoints, database
- **Vue Tests** (`tests/components/`): Component rendering, user interactions, props
- **E2E Tests** (`e2e/`): Full application workflows across browsers (Chromium, Firefox, WebKit)

All tests use the TDD approach and should be written before implementing features.

## Key Features Explained

### Chat System

The chat interface provides:
- Real-time messaging with AI tutor
- Message history persistence
- Typing indicators
- Conversation management (create, delete)
- Mobile-responsive design

### Insights Panel

Provides real-time feedback on:
- Grammar usage
- Vocabulary diversity
- Common mistakes
- Learning progress

Powered by the `AnalyzeRecentMessages` job that processes chat history asynchronously.

### Language Configuration

Users can configure:
- **Native Language**: Their mother tongue
- **Target Language**: Language they want to learn
- **Proficiency Level**: CEFR level (A1-C2)

These settings personalize the AI tutor's responses.

## API Integration

### LangGPT Service

CefrSync integrates with the LangGPT microservice for:
- Progress evaluation
- Language analysis
- CEFR-level assessments

Configuration in `config/services.php`:

```php
'langgpt' => [
    'base_url' => env('LANGGPT_BASE_URL', 'http://localhost:8000'),
    'api_key' => env('LANGGPT_API_KEY'),
],
```

### OpenAI Integration

Used for conversational AI via `openai-php/client`:

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
],
```

## Development Commands

### Composer Shortcuts

```bash
composer setup      # Full setup (install, migrate, build)
composer dev        # Start dev environment (server, queue, vite, logs)
composer fresh      # Fresh database with seeders
```

### Artisan Commands

```bash
art tinker          # Laravel REPL
art migrate:fresh   # Reset database
art queue:work      # Process jobs
art test            # Run tests
```

### NPM Scripts

```bash
npm run dev         # Vite dev server
npm run build       # Production build
npm run preview     # Preview production build
npm test            # Run tests
npm run type-check  # TypeScript validation
npm run lint        # Lint code
```

## Code Style

### PHP
- Follow PSR-12 coding standards
- Use Laravel best practices
- Type hint everything
- Write descriptive variable names

### TypeScript/Vue
- Use TypeScript for all new code
- Follow Vue 3 Composition API patterns
- Use `<script setup lang="ts">`
- Props and emits must be typed

### Testing
- Follow TDD: write tests first
- Maintain high test coverage
- Test user workflows, not implementation details
- Use descriptive test names

## Architecture

### Backend Pattern
- **Controllers**: Handle HTTP requests, return Inertia responses
- **Services**: Business logic, external API calls
- **Jobs**: Asynchronous processing
- **Models**: Eloquent ORM with relationships

### Frontend Pattern
- **Pages**: Top-level Inertia pages
- **Components**: Reusable Vue components
- **Composables**: Shared reactive logic
- **Types**: TypeScript interfaces and types

### No Dual API Layer
CefrSync uses Inertia.js, so controllers return `Inertia::render()` instead of JSON. Frontend components receive data as props.

## Contributing

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Write tests** for your changes
4. **Implement the feature**
5. **Ensure all tests pass**: `php artisan test && npm test`
6. **Build assets**: `npm run build`
7. **Commit changes**: `git commit -m 'feat: add amazing feature'`
8. **Push to branch**: `git push origin feature/amazing-feature`
9. **Open a Pull Request**

### Commit Message Convention

Follow conventional commits:
- `feat:` New features
- `fix:` Bug fixes
- `test:` Test additions/modifications
- `refactor:` Code refactoring
- `docs:` Documentation updates
- `style:` Code style changes
- `chore:` Build process, dependencies

## Troubleshooting

### Frontend not updating
```bash
npm run build  # Always rebuild after JS/TS/Vue changes
```

### Queue not processing
```bash
php artisan queue:restart
php artisan queue:work
```

### Database issues
```bash
php artisan migrate:fresh --seed
```

### Clear caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## License

All rights reserved.

## Support

For issues or questions, please contact the development team.
