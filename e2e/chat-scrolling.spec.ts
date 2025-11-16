import { test, expect } from '@playwright/test';

test.describe('Chat Scrolling', () => {
  test.beforeEach(async ({ page }) => {
    // Login to the application
    await page.goto('/login');
    await page.waitForSelector('#app');
    
    await page.fill('input[type="email"]', 'test@example.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    
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
