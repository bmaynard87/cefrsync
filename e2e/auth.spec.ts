import { test, expect } from '@playwright/test';

// TODO: Implement authentication features to make these tests pass
test.describe.skip('Authentication Flows', () => {
  test.describe('Registration', () => {
    test('should display registration form', async ({ page }) => {
      await page.goto('/register');
      
      await expect(page.locator('input[name="first_name"]')).toBeVisible();
      await expect(page.locator('input[name="last_name"]')).toBeVisible();
      await expect(page.locator('input[type="email"]')).toBeVisible();
      await expect(page.locator('input[name="password"]')).toBeVisible();
      await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
      await expect(page.getByRole('button', { name: /register/i })).toBeVisible();
    });

    test('should show validation errors for empty form submission', async ({ page }) => {
      await page.goto('/register');
      
      await page.click('button[type="submit"]');
      
      // Should show validation errors
      await expect(page.locator('text=first name')).toBeVisible();
      await expect(page.locator('text=email')).toBeVisible();
      await expect(page.locator('text=password')).toBeVisible();
    });

    test('should show error for invalid email format', async ({ page }) => {
      await page.goto('/register');
      
      await page.fill('input[name="first_name"]', 'John');
      await page.fill('input[name="last_name"]', 'Doe');
      await page.fill('input[type="email"]', 'invalid-email');
      await page.fill('input[name="password"]', 'password123');
      await page.fill('input[name="password_confirmation"]', 'password123');
      
      await page.click('button[type="submit"]');
      
      await expect(page.locator('text=/valid email/i')).toBeVisible();
    });

    test('should show error for password mismatch', async ({ page }) => {
      await page.goto('/register');
      
      await page.fill('input[name="first_name"]', 'John');
      await page.fill('input[name="last_name"]', 'Doe');
      await page.fill('input[type="email"]', 'john@example.com');
      await page.fill('input[name="password"]', 'password123');
      await page.fill('input[name="password_confirmation"]', 'different123');
      
      await page.click('button[type="submit"]');
      
      await expect(page.locator('text=/password.*confirm/i')).toBeVisible();
    });

    test('should successfully register new user and redirect to language-chat', async ({ page }) => {
      const timestamp = Date.now();
      const email = `testuser${timestamp}@example.com`;
      
      await page.goto('/register');
      
      await page.fill('input[name="first_name"]', 'Test');
      await page.fill('input[name="last_name"]', 'User');
      await page.fill('input[type="email"]', email);
      await page.fill('input[name="password"]', 'password123');
      await page.fill('input[name="password_confirmation"]', 'password123');
      
      await page.click('button[type="submit"]');
      
      // Should redirect to language-chat after successful registration
      await page.waitForURL('/language-chat');
      await expect(page).toHaveURL('/language-chat');
    });
  });

  test.describe('Login', () => {
    test('should display login form', async ({ page }) => {
      await page.goto('/login');
      
      await expect(page.locator('input[type="email"]')).toBeVisible();
      await expect(page.locator('input[type="password"]')).toBeVisible();
      await expect(page.getByRole('button', { name: /log in/i })).toBeVisible();
      await expect(page.getByText(/forgot.*password/i)).toBeVisible();
    });

    test('should show validation errors for empty credentials', async ({ page }) => {
      await page.goto('/login');
      
      await page.click('button[type="submit"]');
      
      await expect(page.locator('text=/email.*required/i')).toBeVisible();
      await expect(page.locator('text=/password.*required/i')).toBeVisible();
    });

    test('should show error for invalid credentials', async ({ page }) => {
      await page.goto('/login');
      
      await page.fill('input[type="email"]', 'invalid@example.com');
      await page.fill('input[type="password"]', 'wrongpassword');
      
      await page.click('button[type="submit"]');
      
      await expect(page.locator('text=/credentials.*incorrect/i')).toBeVisible();
    });

    test('should successfully login and redirect to language-chat', async ({ page }) => {
      await page.goto('/login');
      
      await page.fill('input[type="email"]', 'test@example.com');
      await page.fill('input[type="password"]', 'password');
      
      await page.click('button[type="submit"]');
      
      await page.waitForURL('/language-chat');
      await expect(page).toHaveURL('/language-chat');
    });

    test('should remember user if "Remember me" is checked', async ({ page }) => {
      await page.goto('/login');
      
      await page.fill('input[type="email"]', 'test@example.com');
      await page.fill('input[type="password"]', 'password');
      await page.check('input[type="checkbox"]'); // Remember me checkbox
      
      await page.click('button[type="submit"]');
      
      await page.waitForURL('/language-chat');
      
      // Check that remember token cookie is set
      const cookies = await page.context().cookies();
      const rememberCookie = cookies.find(c => c.name.includes('remember'));
      expect(rememberCookie).toBeTruthy();
    });
  });

  test.describe('Logout', () => {
    test('should logout user and redirect to home', async ({ page }) => {
      // Login first
      await page.goto('/login');
      await page.fill('input[type="email"]', 'test@example.com');
      await page.fill('input[type="password"]', 'password');
      await page.click('button[type="submit"]');
      await page.waitForURL('/language-chat');
      
      // Now logout
      await page.click('[data-test="user-menu"]'); // User dropdown
      await page.click('text=/log out/i');
      
      // Should redirect to home page
      await page.waitForURL('/');
      await expect(page).toHaveURL('/');
      
      // Should not be able to access protected routes
      await page.goto('/language-chat');
      await expect(page).toHaveURL('/login');
    });
  });

  test.describe('Password Reset', () => {
    test('should display forgot password form', async ({ page }) => {
      await page.goto('/login');
      await page.click('text=/forgot.*password/i');
      
      await expect(page).toHaveURL('/forgot-password');
      await expect(page.locator('input[type="email"]')).toBeVisible();
      await expect(page.getByRole('button', { name: /email.*reset.*link/i })).toBeVisible();
    });

    test('should show validation error for invalid email', async ({ page }) => {
      await page.goto('/forgot-password');
      
      await page.fill('input[type="email"]', 'invalid-email');
      await page.click('button[type="submit"]');
      
      await expect(page.locator('text=/valid email/i')).toBeVisible();
    });

    test('should send password reset email for valid email', async ({ page }) => {
      await page.goto('/forgot-password');
      
      await page.fill('input[type="email"]', 'test@example.com');
      await page.click('button[type="submit"]');
      
      // Should show success message
      await expect(page.locator('text=/password reset link/i')).toBeVisible();
    });

    test('should show error for non-existent email', async ({ page }) => {
      await page.goto('/forgot-password');
      
      await page.fill('input[type="email"]', 'nonexistent@example.com');
      await page.click('button[type="submit"]');
      
      // Should still show success message (for security - don't reveal if email exists)
      await expect(page.locator('text=/password reset link/i')).toBeVisible();
    });
  });

  test.describe('Protected Routes', () => {
    test('should redirect unauthenticated users to login', async ({ page }) => {
      await page.goto('/language-chat');
      await expect(page).toHaveURL('/login');
      
      await page.goto('/dashboard');
      await expect(page).toHaveURL('/login');
      
      await page.goto('/profile');
      await expect(page).toHaveURL('/login');
    });

    test('should allow authenticated users to access protected routes', async ({ page }) => {
      // Login first
      await page.goto('/login');
      await page.fill('input[type="email"]', 'test@example.com');
      await page.fill('input[type="password"]', 'password');
      await page.click('button[type="submit"]');
      await page.waitForURL('/language-chat');
      
      // Should be able to access protected routes
      await page.goto('/dashboard');
      await expect(page).toHaveURL('/dashboard');
      
      await page.goto('/profile');
      await expect(page).toHaveURL('/profile');
    });
  });
});
