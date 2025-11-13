import { test, expect } from '@playwright/test';

test('has title', async ({ page }) => {
  await page.goto('/');

  // Expect the welcome page to have a title containing "CefrSync"
  await expect(page).toHaveTitle(/CefrSync/);
});

test('can navigate to login', async ({ page }) => {
  await page.goto('/');
  
  // Wait for Vue app to load
  await page.waitForSelector('#app');
  
  // Navigate to login page
  await page.goto('/login');
  
  // Wait for the login page to load
  await page.waitForSelector('#app');
  
  // Verify we're on the login page
  await expect(page).toHaveURL(/\/login/);
});
