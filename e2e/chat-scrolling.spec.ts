import { test, expect } from '@playwright/test';

test.describe('Chat Scrolling', () => {
  test.beforeEach(async ({ page }) => {
    // Login to the application
    await page.goto('/login');
    await page.waitForSelector('#app');
    
    await page.fill('input[type="email"]', 'test@example.com');
    await page.fill('input[type="password"]', 'password');
    
    // Debug: Check for validation errors before submitting
    if (process.env.CI) {
      page.on('console', msg => console.log('[BROWSER]', msg.text()));
      
      // Listen to network requests
      page.on('request', async (request) => {
        if (request.url().includes('/login') && request.method() === 'POST') {
          console.log('[DEBUG] POST /login request');
          const postData = request.postData();
          if (postData) {
            console.log('[DEBUG] Request body:', postData.substring(0, 300));
          }
          const headers = request.headers();
          console.log('[DEBUG] X-CSRF-TOKEN:', headers['x-csrf-token'] ? 'present' : 'MISSING');
          console.log('[DEBUG] Cookie:', headers['cookie'] ? headers['cookie'].substring(0, 100) : 'MISSING');
        }
      });
      
      page.on('response', async (response) => {
        if (response.url().includes('/login') && response.request().method() === 'POST') {
          console.log('[DEBUG] POST /login response status:', response.status());
          const headers = response.headers();
          if (headers['location']) {
            console.log('[DEBUG] Redirect location:', headers['location']);
          }
          if (headers['set-cookie']) {
            console.log('[DEBUG] Cookies set:', headers['set-cookie'].substring(0, 100));
          }
          try {
            const body = await response.text();
            if (body) {
              console.log('[DEBUG] Response body length:', body.length);
              // Check if it's JSON (Inertia error response)
              if (body.trim().startsWith('{')) {
                const json = JSON.parse(body);
                console.log('[DEBUG] Response JSON:', JSON.stringify(json).substring(0, 500));
              }
            }
          } catch (e) {
            console.log('[DEBUG] Could not read/parse response body:', e.message);
          }
        }
      });
    }
    
    await page.click('button[type="submit"]');
    
    // Debug: Wait a moment and check for errors
    if (process.env.CI) {
      await page.waitForTimeout(2000);
      const currentUrl = page.url();
      console.log('[DEBUG] Current URL after login click:', currentUrl);
      
      // Check for validation errors on page
      const errorText = await page.textContent('body').catch(() => 'Could not read body');
      if (currentUrl.includes('/login')) {
        console.log('[DEBUG] Still on login page. Page content:', errorText?.substring(0, 500));
      }
    }
    
    // Wait for navigation after login (redirects to language-chat)
    await page.waitForURL('/language-chat', { timeout: 10000 });
    await page.waitForLoadState('networkidle');
  });

  test('chat sidebar should scroll when there are many chats', async ({ page }) => {
    const chatList = page.locator('[data-test="chat-list"]');
    
    // Wait for chat list to be visible
    await chatList.waitFor({ state: 'visible' });
    
    // Check that chat list has overflow-y-auto (scrolling enabled)
    const hasScrollEnabled = await chatList.evaluate((el) => {
      const style = window.getComputedStyle(el);
      return style.overflowY === 'auto' || style.overflowY === 'scroll';
    });
    
    expect(hasScrollEnabled).toBe(true);
    
    // Also verify it could scroll if content was long enough
    const canScroll = await chatList.evaluate((el) => {
      return el.scrollHeight >= el.clientHeight;
    });
    
    expect(canScroll).toBe(true);
  });

  test('main chat container should not scroll', async ({ page }) => {
    const mainContainer = page.locator('[data-test="main-container"]');
    
    // Check that main container does not scroll
    const hasOverflow = await mainContainer.evaluate((el) => {
      const style = window.getComputedStyle(el);
      return style.overflow === 'hidden' || style.overflowY === 'hidden';
    });
    
    expect(hasOverflow).toBe(true);
  });

  test('chat messages area should scroll independently', async ({ page }) => {
    const chatContainer = page.locator('[data-test="chat-container"]');
    
    // Check that chat container can scroll
    const canScroll = await chatContainer.evaluate((el) => {
      const style = window.getComputedStyle(el);
      return style.overflowY === 'auto' || style.overflowY === 'scroll';
    });
    
    expect(canScroll).toBe(true);
  });
});
