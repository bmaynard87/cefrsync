# CefrSync

A comprehensive AI-powered language learning platform built with Laravel, Inertia.js, and Vue.js. CefrSync provides intelligent conversations, context-aware corrections, progress tracking, and personalized feedback to accelerate language acquisition.

## Overview

CefrSync is a full-stack web application that connects learners with AI tutors for immersive language practice. It integrates with the LangGPT microservice for advanced language processing and leverages OpenAI's GPT-4o-mini for natural, engaging conversations tailored to each learner's proficiency level.

## Features

- 🗣️ **AI-Powered Conversations**: Natural dialogues with an adaptive AI tutor in your target language
- ⚠️ **Context-Aware Corrections**: Real-time detection of critical errors (meaningless, offensive, unnatural, archaic, dangerous) with conversation context
- 📊 **Progress Tracking**: Comprehensive conversation history and learning analytics
- 🎯 **CEFR Level Support**: Tailored content for A1 (beginner) through C2 (mastery) proficiency levels
- 💡 **Intelligent Insights**: Automated analysis of grammar patterns, vocabulary diversity, and common mistakes
- 🌍 **Multi-Language Support**: Learn any language from any native language
- 🔄 **Dynamic Proficiency**: Optional auto-adjustment of CEFR level based on performance
- 🔐 **Secure Authentication**: Laravel Breeze with Google OAuth integration and reCAPTCHA protection
- 🎨 **Modern UI**: Built with Tailwind CSS, shadcn/ui components, and smooth animations
- 📱 **Responsive Design**: Seamless experience on desktop, tablet, and mobile devices
- 🔔 **Real-Time Feedback**: Live insights panel with auto-refresh every 30 seconds

## Tech Stack

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.2+
- **Authentication**: Laravel Breeze (Inertia stack) + Google OAuth
- **Queue**: Redis/Database for background jobs
- **Testing**: Pest PHP (208 tests)
- **Code Style**: Laravel Pint

### Frontend
- **Framework**: Vue.js 3 with Composition API
- **Language**: TypeScript
- **SSR**: Inertia.js for server-side rendering
- **UI Components**: shadcn/ui (Radix Vue)
- **Icons**: Lucide Vue
- **Styling**: Tailwind CSS
- **Build Tool**: Vite 7
- **Testing**: Vitest (222 tests) + Playwright (15 E2E tests)

### External Services
- **LangGPT**: FastAPI microservice at `host.docker.internal:8000` for language processing
- **OpenAI**: GPT-4o-mini for conversation generation
- **reCAPTCHA**: Google reCAPTCHA v3 for bot protection

### Infrastructure
- **Docker**: Laravel Sail for local development
- **Database**: MySQL 8 (or PostgreSQL)
- **Cache/Queue**: Redis

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

# OpenAI API key for conversation generation
OPENAI_API_KEY=sk-your_openai_api_key_here

# LangGPT microservice connection
LANGGPT_BASE_URL=http://host.docker.internal:8000
LANGGPT_API_KEY=lgpt_your_langgpt_api_key_here

# Google OAuth (optional)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# reCAPTCHA v3 (optional but recommended)
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key
```

### Generate LangGPT API Key

```bash
# From CefrSync directory
php artisan langgpt:regenerate-key
```

This command:
1. Calls LangGPT's `/v2/keys` endpoint to generate a new key
2. Updates `LANGGPT_API_KEY` in your `.env` file
3. Clears the config cache

**Note**: Run this command on the host (not inside Docker) to avoid permission issues.

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

CefrSync maintains a comprehensive test suite with **435 total tests** across three layers.

### PHP Tests (Pest) - 208 Tests

Run all PHP tests:

```bash
php artisan test
# or using shorthand
art test
```

Run specific test file:

```bash
art test --filter=CorrectionContextTest
```

Run with parallel execution:

```bash
art test --parallel
```

**Test coverage:**
- Feature tests: Authentication, chat system, corrections, insights, language detection
- Unit tests: Services, models, helpers
- Database: RefreshDatabase trait for isolated tests

### Frontend Tests (Vitest) - 222 Tests

Run all component tests:

```bash
npm test
```

Run in watch mode (recommended for development):

```bash
npm test -- --watch
```

Run with coverage report:

```bash
npm test -- --coverage
```

**Test coverage:**
- Component rendering and props
- User interactions and events
- Composables and utilities
- TypeScript type checking

### End-to-End Tests (Playwright) - 15 Tests

**Prerequisites:**
1. Start Docker stack: `./vendor/bin/sail up -d`
2. Start Vite dev server: `npm run dev`
3. Ensure database is seeded: `./vendor/bin/sail artisan migrate:fresh --seed`

Run E2E tests:

```bash
npm run test:e2e
```

Run with UI mode (recommended for debugging):

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

**Test coverage:**
- Full user workflows across Chromium, Firefox, and WebKit
- Chat scrolling behavior, message persistence
- Service down scenarios and error handling
- See `e2e/README.md` for detailed documentation

## Key Features Explained

### Context-Aware Error Corrections

The correction system provides intelligent, contextual feedback:

- **Real-Time Detection**: Checks every user message for critical errors (meaningless, offensive, unnatural, archaic, dangerous)
- **Conversation Context**: Sends last 3 messages to LangGPT for context-aware corrections
  - Example: "no i love but scared" is understood in context of "Do you have a pet snake?"
- **Localized Feedback**: Corrections displayed in user's native language when enabled
- **Severity Levels**: Critical (red), High (orange), Medium (yellow) with visual indicators
- **Detailed Explanations**: Original text, suggested correction, explanation, context, and recommendations
- **Non-Intrusive**: Corrections appear as chat messages, don't block conversation flow

Implementation: `LanguageChatController::sendMessage()` → `LangGptService::checkCriticalErrors()`

### Chat System

Real-time messaging interface with:

- **Conversation Management**: Create, view, update title, delete chat sessions
- **Message Persistence**: Full history stored in database with timestamps
- **Typing Indicators**: Visual feedback while AI generates response
- **Scroll Behavior**: Auto-scroll to latest message, smooth animations
- **Mobile-Responsive**: Touch-optimized sidebar, collapsible on small screens
- **Session Parameters**: Adjustable target language, proficiency level per conversation

Components: `LanguageChat.vue`, `ChatMessage.vue`, `ChatInput.vue`, `ChatSidebar.vue`

### Insights Panel

Provides automated learning feedback:

- **Real-Time Updates**: Fetches new insights every 30 seconds
- **Analysis Triggers**: Runs after every 10 user messages in target language
- **Insight Types**:
  - Grammar patterns and common mistakes
  - Vocabulary diversity and word choice
  - Proficiency level suggestions
  - Personalized recommendations
- **Background Processing**: `AnalyzeRecentMessages` job runs asynchronously
- **Unread Count**: Badge shows new insights since last check
- **Mark as Read**: Individual or bulk marking functionality

Implementation: `AnalyzeRecentMessages.php` job → `LangGptService::analyzeMessages()`

### Dynamic Proficiency Adjustment

Optional automatic CEFR level updates:

- **Opt-In System**: Users choose whether to enable auto-adjustment during registration
- **Confidence-Based**: Only updates when analysis confidence ≥ 0.7
- **Never Downgrades**: Proficiency can only increase, never decrease
- **Progress Suggestions**: Insights notify users when ready for next level
- **Manual Override**: Users can always manually set their level in settings

Configuration: `ProficiencyOptInController`, `AnalyzeRecentMessages` job

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

### PHP (Laravel Pint)

Format code automatically:

```bash
./vendor/bin/pint
```

Pint follows Laravel's opinionated style guide:
- PSR-12 compliant
- Consistent braces and spacing
- Alphabetically sorted imports

### TypeScript/Vue (ESLint + Prettier)

```bash
npm run lint          # Check for issues
npm run type-check    # Validate TypeScript
```

**Conventions:**
- Use TypeScript for all new code
- Follow Vue 3 Composition API with `<script setup lang="ts">`
- Props and emits must be typed with interfaces
- Use Tailwind CSS classes, avoid custom CSS when possible

### Testing (TDD Philosophy)

1. **Write tests first** before implementation
2. **Red-Green-Refactor**: Fail → Pass → Improve
3. **Test behavior**, not implementation details
4. **Descriptive names**: `it('correction check includes recent conversation context')`
5. **Arrange-Act-Assert**: Clear test structure

## Recent Updates

### Conversation Context for Corrections (Nov 2024)

Critical error checking now uses conversation history for contextual awareness:

- **Context Passing**: Last 3 non-correction messages sent to LangGPT
- **Format**: `{role: 'user'|'assistant', content: string}[]`
- **Benefits**: Corrections understand what user is responding to
- **Example**: "no i love but scared lol" after "Do you have a pet snake?" is correctly interpreted
- **Testing**: 4 comprehensive tests in `CorrectionContextTest.php`

Files changed:
- `LangGptService.php`: Added `context_messages` to payload
- `LanguageChatController.php`: Fetches and formats last 3 messages
- LangGPT API: Accepts optional `context_messages` field

### Improved Correction Message Styling (Nov 2024)

Enhanced readability in dark mode:

- Lighter backgrounds for explanation, context, and recommendations sections
- Improved text contrast (`dark:text-gray-50`)
- Darker error type labels for better visibility
- Better separation between correction elements

### Fixed Missing Component Import (Nov 2024)

- Added missing `Spinner` import to `InsightPanel.vue`
- Resolves Vue warning about unresolved component

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
