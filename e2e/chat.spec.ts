import { test, expect } from '@playwright/test';

// TODO: Implement chat features to make these tests pass
test.describe.skip('Chat Functionality', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[type="email"]', 'test@example.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/language-chat');
  });

  test.describe('Creating Chat Sessions', () => {
    test('should display new chat button', async ({ page }) => {
      const newChatButton = page.locator('[data-test="new-chat-button"]');
      await expect(newChatButton).toBeVisible();
    });

    test('should create new chat session when clicking new chat button', async ({ page }) => {
      const chatListBefore = await page.locator('[data-test="chat-item"]').count();
      
      await page.click('[data-test="new-chat-button"]');
      
      // Wait for new chat to be created
      await page.waitForTimeout(1000);
      
      const chatListAfter = await page.locator('[data-test="chat-item"]').count();
      expect(chatListAfter).toBe(chatListBefore + 1);
    });

    test('should focus on new chat after creation', async ({ page }) => {
      await page.click('[data-test="new-chat-button"]');
      await page.waitForTimeout(1000);
      
      // Message input should be focused
      const messageInput = page.locator('textarea[placeholder*="message"]');
      await expect(messageInput).toBeFocused();
    });

    test('should show empty chat state for new chat', async ({ page }) => {
      await page.click('[data-test="new-chat-button"]');
      await page.waitForTimeout(1000);
      
      // Should have no messages
      const messages = await page.locator('[data-test="message"]').count();
      expect(messages).toBe(0);
      
      // Should show placeholder or welcome message
      await expect(page.locator('text=/start.*conversation/i')).toBeVisible();
    });
  });

  test.describe('Sending Messages', () => {
    test('should send message when clicking send button', async ({ page }) => {
      const messageInput = page.locator('textarea[placeholder*="message"]');
      const sendButton = page.locator('button[type="submit"]');
      
      await messageInput.fill('Hello, this is a test message');
      await sendButton.click();
      
      // Should see the sent message
      await expect(page.locator('text=Hello, this is a test message')).toBeVisible();
    });

    test('should send message when pressing Enter (without Shift)', async ({ page }) => {
      const messageInput = page.locator('textarea[placeholder*="message"]');
      
      await messageInput.fill('Test message via Enter key');
      await messageInput.press('Enter');
      
      await expect(page.locator('text=Test message via Enter key')).toBeVisible();
    });

    test('should add new line when pressing Shift+Enter', async ({ page }) => {
      const messageInput = page.locator('textarea[placeholder*="message"]');
      
      await messageInput.fill('Line 1');
      await page.keyboard.press('Shift+Enter');
      await messageInput.type('Line 2');
      
      const value = await messageInput.inputValue();
      expect(value).toContain('\n');
    });

    test('should clear input after sending message', async ({ page }) => {
      const messageInput = page.locator('textarea[placeholder*="message"]');
      
      await messageInput.fill('Message to send');
      await page.click('button[type="submit"]');
      
      await expect(messageInput).toHaveValue('');
    });

    test('should disable send button while sending', async ({ page }) => {
      const messageInput = page.locator('textarea[placeholder*="message"]');
      const sendButton = page.locator('button[type="submit"]');
      
      await messageInput.fill('Test message');
      await sendButton.click();
      
      // Button should be disabled during sending
      await expect(sendButton).toBeDisabled();
    });

    test('should show typing indicator while waiting for AI response', async ({ page }) => {
      const messageInput = page.locator('textarea[placeholder*="message"]');
      
      await messageInput.fill('Hello AI');
      await page.click('button[type="submit"]');
      
      // Should show typing indicator
      await expect(page.locator('[data-test="typing-indicator"]')).toBeVisible();
    });

    test('should receive AI response after sending message', async ({ page }) => {
      const messageInput = page.locator('textarea[placeholder*="message"]');
      
      await messageInput.fill('How are you?');
      await page.click('button[type="submit"]');
      
      // Wait for AI response (with timeout)
      await expect(page.locator('[data-test="ai-message"]').first()).toBeVisible({ timeout: 10000 });
    });

    test('should scroll to bottom after receiving new message', async ({ page }) => {
      const chatContainer = page.locator('[data-test="chat-container"]');
      
      // Send a message
      await page.fill('textarea[placeholder*="message"]', 'Test scroll');
      await page.click('button[type="submit"]');
      
      // Wait for response
      await page.waitForTimeout(2000);
      
      // Check if scrolled to bottom
      const isAtBottom = await chatContainer.evaluate((el) => {
        return Math.abs(el.scrollHeight - el.scrollTop - el.clientHeight) < 10;
      });
      
      expect(isAtBottom).toBe(true);
    });
  });

  test.describe('Switching Chat Sessions', () => {
    test('should display list of chat sessions', async ({ page }) => {
      const chatItems = page.locator('[data-test="chat-item"]');
      const count = await chatItems.count();
      
      expect(count).toBeGreaterThan(0);
    });

    test('should switch to different chat when clicking chat item', async ({ page }) => {
      const firstChat = page.locator('[data-test="chat-item"]').first();
      const secondChat = page.locator('[data-test="chat-item"]').nth(1);
      
      // Get chat titles
      const firstTitle = await firstChat.textContent();
      const secondTitle = await secondChat.textContent();
      
      // Click second chat
      await secondChat.click();
      await page.waitForTimeout(500);
      
      // Should show second chat's messages
      // (Verify by checking active state or header)
      await expect(page.locator('[data-test="chat-header"]')).toContainText(secondTitle || '');
    });

    test('should highlight active chat session', async ({ page }) => {
      const firstChat = page.locator('[data-test="chat-item"]').first();
      
      await firstChat.click();
      await page.waitForTimeout(500);
      
      // Should have active class or styling
      const className = await firstChat.getAttribute('class');
      expect(className).toContain('active');
    });

    test('should load messages for selected chat', async ({ page }) => {
      const chatWithMessages = page.locator('[data-test="chat-item"]').first();
      
      await chatWithMessages.click();
      await page.waitForTimeout(1000);
      
      // Should display messages
      const messageCount = await page.locator('[data-test="message"]').count();
      expect(messageCount).toBeGreaterThan(0);
    });

    test('should persist chat selection on page reload', async ({ page }) => {
      const secondChat = page.locator('[data-test="chat-item"]').nth(1);
      const chatId = await secondChat.getAttribute('data-chat-id');
      
      await secondChat.click();
      await page.waitForTimeout(500);
      
      // Reload page
      await page.reload();
      await page.waitForLoadState('networkidle');
      
      // Should still be on the same chat
      const activeChat = page.locator('[data-test="chat-item"].active');
      const activeChatId = await activeChat.getAttribute('data-chat-id');
      
      expect(activeChatId).toBe(chatId);
    });
  });

  test.describe('Deleting Chat Sessions', () => {
    test('should show delete button for chat sessions', async ({ page }) => {
      const firstChat = page.locator('[data-test="chat-item"]').first();
      
      // Hover to reveal delete button
      await firstChat.hover();
      
      const deleteButton = firstChat.locator('[data-test="delete-chat-button"]');
      await expect(deleteButton).toBeVisible();
    });

    test('should show confirmation dialog when clicking delete', async ({ page }) => {
      const firstChat = page.locator('[data-test="chat-item"]').first();
      
      await firstChat.hover();
      await firstChat.locator('[data-test="delete-chat-button"]').click();
      
      // Should show confirmation dialog
      await expect(page.locator('text=/delete.*chat/i')).toBeVisible();
      await expect(page.getByRole('button', { name: /cancel/i })).toBeVisible();
      await expect(page.getByRole('button', { name: /delete|confirm/i })).toBeVisible();
    });

    test('should cancel deletion when clicking cancel', async ({ page }) => {
      const chatCountBefore = await page.locator('[data-test="chat-item"]').count();
      const firstChat = page.locator('[data-test="chat-item"]').first();
      
      await firstChat.hover();
      await firstChat.locator('[data-test="delete-chat-button"]').click();
      
      // Click cancel
      await page.getByRole('button', { name: /cancel/i }).click();
      
      // Chat count should remain the same
      const chatCountAfter = await page.locator('[data-test="chat-item"]').count();
      expect(chatCountAfter).toBe(chatCountBefore);
    });

    test('should delete chat when confirming deletion', async ({ page }) => {
      const chatCountBefore = await page.locator('[data-test="chat-item"]').count();
      const lastChat = page.locator('[data-test="chat-item"]').last();
      
      await lastChat.hover();
      await lastChat.locator('[data-test="delete-chat-button"]').click();
      
      // Confirm deletion
      await page.getByRole('button', { name: /delete|confirm/i }).click();
      await page.waitForTimeout(1000);
      
      // Chat count should decrease
      const chatCountAfter = await page.locator('[data-test="chat-item"]').count();
      expect(chatCountAfter).toBe(chatCountBefore - 1);
    });

    test('should switch to another chat after deleting active chat', async ({ page }) => {
      const firstChat = page.locator('[data-test="chat-item"]').first();
      
      // Select and delete first chat
      await firstChat.click();
      await page.waitForTimeout(500);
      
      await firstChat.hover();
      await firstChat.locator('[data-test="delete-chat-button"]').click();
      await page.getByRole('button', { name: /delete|confirm/i }).click();
      await page.waitForTimeout(1000);
      
      // Should auto-select another chat
      const activeChats = await page.locator('[data-test="chat-item"].active').count();
      expect(activeChats).toBe(1);
    });

    test('should show empty state when all chats are deleted', async ({ page }) => {
      // Delete all chats
      while (await page.locator('[data-test="chat-item"]').count() > 0) {
        const chat = page.locator('[data-test="chat-item"]').first();
        await chat.hover();
        await chat.locator('[data-test="delete-chat-button"]').click();
        await page.getByRole('button', { name: /delete|confirm/i }).click();
        await page.waitForTimeout(500);
      }
      
      // Should show empty state
      await expect(page.locator('text=/no.*chat/i')).toBeVisible();
      await expect(page.locator('[data-test="new-chat-button"]')).toBeVisible();
    });
  });

  test.describe('Chat Title Editing', () => {
    test('should display chat title in header', async ({ page }) => {
      const chatHeader = page.locator('[data-test="chat-header"]');
      await expect(chatHeader).toBeVisible();
    });

    test('should allow editing chat title', async ({ page }) => {
      const editButton = page.locator('[data-test="edit-title-button"]');
      
      await editButton.click();
      
      // Should show input field
      const titleInput = page.locator('[data-test="title-input"]');
      await expect(titleInput).toBeVisible();
      await expect(titleInput).toBeFocused();
    });

    test('should save new chat title', async ({ page }) => {
      await page.click('[data-test="edit-title-button"]');
      
      const titleInput = page.locator('[data-test="title-input"]');
      await titleInput.fill('My Custom Chat Title');
      await titleInput.press('Enter');
      
      // Should show new title
      await expect(page.locator('[data-test="chat-header"]')).toContainText('My Custom Chat Title');
    });

    test('should cancel title editing on Escape', async ({ page }) => {
      const originalTitle = await page.locator('[data-test="chat-header"]').textContent();
      
      await page.click('[data-test="edit-title-button"]');
      
      const titleInput = page.locator('[data-test="title-input"]');
      await titleInput.fill('Temporary Title');
      await titleInput.press('Escape');
      
      // Should revert to original title
      await expect(page.locator('[data-test="chat-header"]')).toContainText(originalTitle || '');
    });

    test('should update title in sidebar when changed', async ({ page }) => {
      const newTitle = 'Updated Sidebar Title';
      
      await page.click('[data-test="edit-title-button"]');
      await page.fill('[data-test="title-input"]', newTitle);
      await page.press('[data-test="title-input"]', 'Enter');
      
      // Check sidebar
      const activeChat = page.locator('[data-test="chat-item"].active');
      await expect(activeChat).toContainText(newTitle);
    });
  });
});
