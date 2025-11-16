import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import GoogleSignInButton from '@/components/GoogleSignInButton.vue';

describe('GoogleSignInButton', () => {
  beforeEach(() => {
    // Mock window.google
    vi.stubGlobal('google', {
      accounts: {
        id: {
          initialize: vi.fn(),
          renderButton: vi.fn(),
        },
      },
    });
  });

  it('renders the button container', () => {
    const wrapper = mount(GoogleSignInButton, {
      props: {
        clientId: 'test-client-id',
      },
    });

    expect(wrapper.find('[data-test="google-signin-container"]').exists()).toBe(true);
  });

  it('initializes Google Identity Services on mount', async () => {
    const mockInitialize = vi.fn();
    vi.stubGlobal('google', {
      accounts: {
        id: {
          initialize: mockInitialize,
          renderButton: vi.fn(),
        },
      },
    });

    mount(GoogleSignInButton, {
      props: {
        clientId: 'test-client-id',
      },
    });

    // Wait for next tick to allow onMounted to execute
    await new Promise(resolve => setTimeout(resolve, 0));

    expect(mockInitialize).toHaveBeenCalledWith({
      client_id: 'test-client-id',
      callback: expect.any(Function),
    });
  });

  it('renders Google button with correct configuration', async () => {
    const mockRenderButton = vi.fn();
    vi.stubGlobal('google', {
      accounts: {
        id: {
          initialize: vi.fn(),
          renderButton: mockRenderButton,
        },
      },
    });

    const wrapper = mount(GoogleSignInButton, {
      props: {
        clientId: 'test-client-id',
      },
    });

    await new Promise(resolve => setTimeout(resolve, 0));

    const buttonElement = wrapper.find('[data-test="google-signin-button"]').element;
    expect(mockRenderButton).toHaveBeenCalledWith(
      buttonElement,
      {
        theme: 'outline',
        size: 'large',
        text: 'continue_with',
        shape: 'rectangular',
        width: expect.any(Number),
      }
    );
  });

  it('handles successful sign-in response', async () => {
    let capturedCallback: Function | null = null;
    
    vi.stubGlobal('google', {
      accounts: {
        id: {
          initialize: vi.fn((config: any) => {
            capturedCallback = config.callback;
          }),
          renderButton: vi.fn(),
        },
      },
    });

    const wrapper = mount(GoogleSignInButton, {
      props: {
        clientId: 'test-client-id',
      },
    });

    await new Promise(resolve => setTimeout(resolve, 0));

    // Simulate successful sign-in
    const mockResponse = { credential: 'mock-jwt-token' };
    capturedCallback?.(mockResponse);

    // Should emit sign-in event
    expect(wrapper.emitted('signin')).toBeTruthy();
    expect(wrapper.emitted('signin')?.[0]).toEqual([mockResponse]);
  });

  it('handles sign-in error', async () => {
    let capturedCallback: Function | null = null;
    
    vi.stubGlobal('google', {
      accounts: {
        id: {
          initialize: vi.fn((config: any) => {
            capturedCallback = config.callback;
          }),
          renderButton: vi.fn(),
        },
      },
    });

    const wrapper = mount(GoogleSignInButton, {
      props: {
        clientId: 'test-client-id',
      },
    });

    await new Promise(resolve => setTimeout(resolve, 0));

    // Simulate error
    const mockError = { error: 'access_denied' };
    capturedCallback?.(mockError);

    // Should emit error event
    expect(wrapper.emitted('error')).toBeTruthy();
    expect(wrapper.emitted('error')?.[0]).toEqual([mockError]);
  });

  it('does not initialize if Google SDK is not loaded', () => {
    vi.stubGlobal('google', undefined);
    
    const wrapper = mount(GoogleSignInButton, {
      props: {
        clientId: 'test-client-id',
      },
    });

    // Should render but not crash
    expect(wrapper.find('[data-test="google-signin-container"]').exists()).toBe(true);
  });
});
