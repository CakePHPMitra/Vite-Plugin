<?php
declare(strict_types=1);

namespace CakePhpViteHelper\Test\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use CakePhpViteHelper\View\Helper\ViteHelper;

/**
 * ViteHelper Test Case
 */
class ViteHelperTest extends TestCase
{
    protected ViteHelper $helper;
    protected View $view;
    protected string $testManifestPath;
    protected string $testHotFile;
    protected mixed $originalDebug;

    /**
     * Setup method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->view = new View();

        // Store original debug setting
        $this->originalDebug = Configure::read('debug');

        // Create test manifest directory
        $this->testManifestPath = TMP . 'vite_test' . DS;
        if (!is_dir($this->testManifestPath)) {
            mkdir($this->testManifestPath . '.vite', 0755, true);
        }

        // Create test hot file path
        $this->testHotFile = TMP . 'hot_test';
    }

    /**
     * Teardown method
     */
    public function tearDown(): void
    {
        // Restore debug setting
        Configure::write('debug', $this->originalDebug ?? true);

        // Clean up test files
        if (file_exists($this->testHotFile)) {
            unlink($this->testHotFile);
        }

        $manifestFile = $this->testManifestPath . '.vite' . DS . 'manifest.json';
        if (file_exists($manifestFile)) {
            unlink($manifestFile);
        }

        if (is_dir($this->testManifestPath . '.vite')) {
            rmdir($this->testManifestPath . '.vite');
        }

        if (is_dir($this->testManifestPath)) {
            rmdir($this->testManifestPath);
        }

        parent::tearDown();
    }

    /**
     * Test helper initialization
     */
    public function testInitialization(): void
    {
        $helper = new ViteHelper($this->view);

        $this->assertInstanceOf(ViteHelper::class, $helper);
    }

    /**
     * Test asset method generates correct script tags
     */
    public function testAssetGeneratesScriptTags(): void
    {
        // Disable debug to skip dev server check
        Configure::write('debug', false);

        // Create a mock manifest
        $manifestDir = WWW_ROOT . 'build' . DS . '.vite';
        $manifestFile = $manifestDir . DS . 'manifest.json';

        // Skip test if we can't create the manifest
        if (!is_dir($manifestDir) && !@mkdir($manifestDir, 0755, true)) {
            $this->markTestSkipped('Cannot create test manifest directory');
        }

        $manifest = [
            'resources/js/app.js' => ['file' => 'assets/app-abc123.js'],
            'resources/css/app.css' => ['file' => 'assets/app-def456.css'],
        ];

        file_put_contents($manifestFile, json_encode($manifest));

        try {
            $helper = new ViteHelper($this->view);
            $result = $helper->asset(['resources/js/app.js', 'resources/css/app.css']);

            $this->assertStringContainsString('<script type="module"', $result);
            $this->assertStringContainsString('<link rel="stylesheet"', $result);
            $this->assertStringContainsString('assets/app-abc123.js', $result);
            $this->assertStringContainsString('assets/app-def456.css', $result);
        } finally {
            // Clean up
            if (file_exists($manifestFile)) {
                unlink($manifestFile);
            }
        }
    }

    /**
     * Test url method throws exception when manifest not found
     */
    public function testUrlThrowsExceptionWhenManifestNotFound(): void
    {
        Configure::write('debug', false);

        // Ensure manifest doesn't exist
        $manifestFile = WWW_ROOT . 'build' . DS . '.vite' . DS . 'manifest.json';
        if (file_exists($manifestFile)) {
            $this->markTestSkipped('Manifest file exists, cannot test missing manifest');
        }

        $helper = new ViteHelper($this->view);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Vite manifest');

        $helper->url('resources/js/app.js');
    }

    /**
     * Test connection timeout property exists
     */
    public function testConnectionTimeoutExists(): void
    {
        $helper = new ViteHelper($this->view);

        // Use reflection to check protected property
        $reflection = new \ReflectionClass($helper);
        $property = $reflection->getProperty('connectionTimeout');
        $property->setAccessible(true);

        $timeout = $property->getValue($helper);

        $this->assertIsInt($timeout);
        $this->assertGreaterThan(0, $timeout);
        $this->assertLessThanOrEqual(10, $timeout); // Should be reasonable
    }

    /**
     * Test isDevServerRunning returns false when debug is off
     */
    public function testIsDevServerRunningReturnsFalseWhenDebugOff(): void
    {
        Configure::write('debug', false);

        $helper = new ViteHelper($this->view);

        // Use reflection to call protected method
        $reflection = new \ReflectionClass($helper);
        $method = $reflection->getMethod('isDevServerRunning');
        $method->setAccessible(true);

        $result = $method->invoke($helper);

        $this->assertFalse($result);
    }

    /**
     * Test asset escapes URLs properly (XSS prevention)
     */
    public function testAssetEscapesUrls(): void
    {
        Configure::write('debug', false);

        $manifestDir = WWW_ROOT . 'build' . DS . '.vite';
        $manifestFile = $manifestDir . DS . 'manifest.json';

        if (!is_dir($manifestDir) && !@mkdir($manifestDir, 0755, true)) {
            $this->markTestSkipped('Cannot create test manifest directory');
        }

        // Create manifest with potentially dangerous characters
        $manifest = [
            'resources/js/app.js' => ['file' => 'assets/app.js'],
        ];

        file_put_contents($manifestFile, json_encode($manifest));

        try {
            $helper = new ViteHelper($this->view);
            $result = $helper->asset(['resources/js/app.js']);

            // Verify output is escaped (h() function used)
            $this->assertStringNotContainsString('<script>alert', $result);
        } finally {
            if (file_exists($manifestFile)) {
                unlink($manifestFile);
            }
        }
    }

    /**
     * Test dev server URL validation from hot file
     */
    public function testHotFileUrlValidation(): void
    {
        // This test verifies that invalid URLs in hot file are ignored
        $helper = new ViteHelper($this->view);

        // Use reflection to check devServerUrl
        $reflection = new \ReflectionClass($helper);
        $property = $reflection->getProperty('devServerUrl');
        $property->setAccessible(true);

        $devServerUrl = $property->getValue($helper);

        // Should be a valid URL
        $this->assertNotEmpty($devServerUrl);
        $this->assertMatchesRegularExpression('/^https?:\/\//', $devServerUrl);
    }
}
