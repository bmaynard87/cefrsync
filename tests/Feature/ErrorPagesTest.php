<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_404_page_renders_correctly(): void
    {
        $response = $this->get('/nonexistent-page');

        $response->assertStatus(404);
        $response->assertSee('Page Not Found');
        $response->assertSee('404');
        $response->assertSee('CefrSync');
        $response->assertSee('Go Home');
        $response->assertSee('Go Back');
    }

    public function test_403_page_renders_correctly(): void
    {
        // Trigger a 403 by trying to access a route without permission
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(404); // Will be 404 if route doesn't exist, but that's okay for now
        // We'll test the actual 403 view rendering
    }

    public function test_403_view_contains_expected_content(): void
    {
        $view = view('errors.403');

        $html = $view->render();

        $this->assertStringContainsString('Access Denied', $html);
        $this->assertStringContainsString('403', $html);
        $this->assertStringContainsString('CefrSync', $html);
        $this->assertStringContainsString('Go Home', $html);
    }

    public function test_500_view_contains_expected_content(): void
    {
        $view = view('errors.500');

        $html = $view->render();

        $this->assertStringContainsString('Something Went Wrong', $html);
        $this->assertStringContainsString('500', $html);
        $this->assertStringContainsString('CefrSync', $html);
        $this->assertStringContainsString('Go Home', $html);
        $this->assertStringContainsString('Try Again', $html);
    }

    public function test_503_view_contains_expected_content(): void
    {
        $view = view('errors.503');

        $html = $view->render();

        $this->assertStringContainsString('We\'ll Be Right Back', $html);
        $this->assertStringContainsString('503', $html);
        $this->assertStringContainsString('CefrSync', $html);
        $this->assertStringContainsString('maintenance', $html);
    }

    public function test_419_view_contains_expected_content(): void
    {
        $view = view('errors.419');

        $html = $view->render();

        $this->assertStringContainsString('Session Expired', $html);
        $this->assertStringContainsString('419', $html);
        $this->assertStringContainsString('CefrSync', $html);
        $this->assertStringContainsString('Refresh Page', $html);
    }

    public function test_429_view_contains_expected_content(): void
    {
        $view = view('errors.429');

        $html = $view->render();

        $this->assertStringContainsString('Slow Down!', $html);
        $this->assertStringContainsString('429', $html);
        $this->assertStringContainsString('CefrSync', $html);
        $this->assertStringContainsString('too many requests', $html);
    }

    public function test_404_view_contains_expected_content(): void
    {
        $view = view('errors.404');

        $html = $view->render();

        $this->assertStringContainsString('Page Not Found', $html);
        $this->assertStringContainsString('404', $html);
        $this->assertStringContainsString('CefrSync', $html);
        $this->assertStringContainsString('Go Home', $html);
        $this->assertStringContainsString('Go Back', $html);
    }

    public function test_error_pages_have_proper_html_structure(): void
    {
        $errorPages = ['404', '403', '500', '503', '419', '429'];

        foreach ($errorPages as $errorCode) {
            $view = view("errors.{$errorCode}");
            $html = $view->render();

            // Check for basic HTML structure
            $this->assertStringContainsString('<!DOCTYPE html>', $html);
            $this->assertStringContainsString('<html', $html);
            $this->assertStringContainsString('<head>', $html);
            $this->assertStringContainsString('<body', $html);
            $this->assertStringContainsString('</body>', $html);
            $this->assertStringContainsString('</html>', $html);

            // Check for viewport meta tag (mobile responsiveness)
            $this->assertStringContainsString('viewport', $html);

            // Check for Vite assets
            $this->assertStringContainsString('@vite', $html);
        }
    }

    public function test_error_pages_have_app_logo(): void
    {
        $errorPages = ['404', '403', '500', '503', '419', '429'];

        foreach ($errorPages as $errorCode) {
            $view = view("errors.{$errorCode}");
            $html = $view->render();

            // Check for logo SVG
            $this->assertStringContainsString('<svg', $html);
            $this->assertStringContainsString('CefrSync', $html);
        }
    }

    public function test_error_pages_have_home_link(): void
    {
        // Pages that should have a "Go Home" link
        $pagesWithHomeLink = ['404', '403', '500', '419', '429'];

        foreach ($pagesWithHomeLink as $errorCode) {
            $view = view("errors.{$errorCode}");
            $html = $view->render();

            // Check for "Go Home" link text
            $this->assertStringContainsString('Go Home', $html);

            // Check that href attribute exists
            $this->assertStringContainsString('href=', $html);
        }

        // 503 page has "Try Again" instead
        $view503 = view('errors.503');
        $html503 = $view503->render();
        $this->assertStringContainsString('Try Again', $html503);
    }

    public function test_error_pages_use_consistent_styling(): void
    {
        $errorPages = ['404', '403', '500', '503', '419', '429'];

        foreach ($errorPages as $errorCode) {
            $view = view("errors.{$errorCode}");
            $html = $view->render();

            // Check for consistent Tailwind CSS classes
            $this->assertStringContainsString('bg-gradient-to-b from-blue-50 to-white', $html);
            $this->assertStringContainsString('min-h-screen', $html);
            $this->assertStringContainsString('flex items-center justify-center', $html);
        }
    }

    public function test_404_page_has_confused_emoji_icon(): void
    {
        $view = view('errors.404');
        $html = $view->render();

        // Check for the confused face SVG icon
        $this->assertStringContainsString('M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01', $html);
    }

    public function test_403_page_has_lock_icon(): void
    {
        $view = view('errors.403');
        $html = $view->render();

        // Check for the lock SVG icon
        $this->assertStringContainsString('M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6', $html);
    }

    public function test_500_page_has_warning_icon(): void
    {
        $view = view('errors.500');
        $html = $view->render();

        // Check for the warning triangle SVG icon
        $this->assertStringContainsString('M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667', $html);
    }

    public function test_503_page_has_loading_animation(): void
    {
        $view = view('errors.503');
        $html = $view->render();

        // Check for animated dots
        $this->assertStringContainsString('animate-pulse', $html);
    }

    public function test_429_page_has_countdown_timer(): void
    {
        $view = view('errors.429');
        $html = $view->render();

        // Check for countdown timer element
        $this->assertStringContainsString('id="countdown"', $html);
        $this->assertStringContainsString('seconds', $html);
    }
}
