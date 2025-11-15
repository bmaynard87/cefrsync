<script setup lang="ts">
import { onMounted, ref } from 'vue';

interface GoogleSignInResponse {
  credential: string;
  select_by?: string;
}

interface GoogleSignInError {
  error: string;
  details?: string;
}

const props = defineProps<{
  clientId: string;
}>();

const emit = defineEmits<{
  signin: [response: GoogleSignInResponse];
  error: [error: GoogleSignInError];
}>();

const buttonRef = ref<HTMLElement | null>(null);

const initializeGoogleSignIn = () => {
  if (typeof window === 'undefined' || !window.google) {
    console.warn('Google Identity Services SDK not loaded');
    return;
  }

  try {
    window.google.accounts.id.initialize({
      client_id: props.clientId,
      callback: handleCredentialResponse,
    });

    if (buttonRef.value) {
      window.google.accounts.id.renderButton(
        buttonRef.value,
        {
          theme: 'outline',
          size: 'large',
          text: 'continue_with',
          shape: 'rectangular',
          width: buttonRef.value.offsetWidth || 300,
        }
      );
    }
  } catch (error) {
    console.error('Failed to initialize Google Sign-In:', error);
  }
};

const handleCredentialResponse = (response: GoogleSignInResponse | GoogleSignInError) => {
  if ('error' in response) {
    emit('error', response);
  } else if ('credential' in response) {
    emit('signin', response);
  }
};

onMounted(() => {
  initializeGoogleSignIn();
});
</script>

<template>
  <div data-test="google-signin-container" class="w-full">
    <div ref="buttonRef" data-test="google-signin-button" class="w-full"></div>
  </div>
</template>

<style scoped>
/* Ensure Google button fills container width */
:deep(.nsm7Bb-HzV7m-LgbsSe) {
  width: 100% !important;
}
</style>
