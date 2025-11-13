# End-to-End Tests with Playwright

## Running E2E Tests

These tests run against the full Docker-based application stack.

### Prerequisites

1. **Start the Docker stack:**
   ```bash
   ./vendor/bin/sail up -d
   ```

2. **Start the Vite dev server** (in a separate terminal):
   ```bash
   npm run dev
   ```

3. **Ensure database is seeded:**
   ```bash
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```

### Run Tests

```bash
# Run all E2E tests
npm run test:e2e

# Run with UI mode (recommended for development)
npm run test:e2e:ui

# Run in headed mode (see the browser)
npm run test:e2e:headed

# Debug specific test
npm run test:e2e:debug
```

## Test Structure

- `e2e/example.spec.ts` - Basic navigation and page load tests
- `e2e/chat-scrolling.spec.ts` - Chat UI scrolling functionality tests

## Notes

- Tests expect the app to be running on `http://localhost` (port 80)
- Tests use `test@example.com` / `password` credentials from seeded data
- The chat scrolling tests verify the fixes for sidebar and message area scrolling
