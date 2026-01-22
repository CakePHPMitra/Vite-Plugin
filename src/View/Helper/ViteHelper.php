<?php

declare(strict_types=1);

namespace CakePhpViteHelper\View\Helper;

use Cake\View\Helper;
use Cake\Core\Configure;
use Cake\Routing\Router;

class ViteHelper extends Helper
{
  protected string $manifestPath;
  protected string $devServerUrl;
  protected string $hotFile = ROOT . DS . 'hot';

  public function __construct($view, array $config = [])
  {
    parent::__construct($view, $config);
    $this->manifestPath = WWW_ROOT . 'build' . DS . '.vite' . DS . 'manifest.json';
    $this->devServerUrl = 'http://localhost:5173';

    if (file_exists($this->hotFile)) {
      $url = trim(file_get_contents($this->hotFile));
      if (filter_var($url, FILTER_VALIDATE_URL)) {
        $this->devServerUrl = $url;
      }
    }
  }

  /**
   * Check timeout for dev server connection (seconds)
   */
  protected int $connectionTimeout = 2;

  protected function isDevServerRunning(): bool
  {
    if (Configure::read('debug') === false) {
      return false;
    }

    // SECURITY: Use stream context with timeout to prevent DoS via slow server
    $context = stream_context_create([
      'http' => [
        'timeout' => $this->connectionTimeout,
        'method' => 'HEAD', // Only fetch headers, not body
      ],
      'ssl' => [
        'verify_peer' => false, // Dev server typically uses self-signed cert
        'verify_peer_name' => false,
      ],
    ]);

    $headers = @get_headers($this->devServerUrl, false, $context);
    return $headers && strpos($headers[0], '200') !== false;
  }

  public function url(string $entry): string
  {
    if ($this->isDevServerRunning()) {
      return $this->devServerUrl . '/' . $entry;
    }
    if (!file_exists($this->manifestPath)) {
      $hotExists = file_exists($this->hotFile);
      $message = "Vite manifest not found at: {$this->manifestPath}\n\n";

      if ($hotExists) {
        $message .= "The 'hot' file exists but the dev server is not responding.\n";
        $message .= "Hot file URL: {$this->devServerUrl}\n\n";
        $message .= "Possible fixes:\n";
        $message .= "1. Start Vite: npm run dev (or: ddev exec npm run dev)\n";
        $message .= "2. Check if the URL in the 'hot' file is correct\n";
        $message .= "3. For DDEV: ensure Vite port 5173 is exposed and allowedHosts is configured\n";
        $message .= "4. Delete the 'hot' file to use production assets";
      } else {
        $message .= "No 'hot' file found and no manifest exists.\n\n";
        $message .= "For development: run 'npm run dev' to start Vite\n";
        $message .= "For production: run 'npm run build' to generate the manifest";
      }

      throw new \Exception($message);
    }
    $manifest = json_decode(file_get_contents($this->manifestPath), true);

    if (!isset($manifest[$entry])) {
      $availableEntries = implode(', ', array_keys($manifest));
      throw new \Exception(
        "Entry '{$entry}' not found in Vite manifest.\n" .
        "Available entries: {$availableEntries}\n\n" .
        "Make sure the entry is listed in vite.config.js 'input' array."
      );
    }

    return Router::url('/build/' . $manifest[$entry]['file']);
  }

  public function asset(array $entries): string
  {
    $tags = [];
    if ($this->isDevServerRunning()) {
      $tags[] = '<script type="module" src="' . h($this->devServerUrl) . '/@vite/client"></script>';
    }
    foreach ($entries as $entry) {
      $url = h($this->url($entry));
      if (str_ends_with($entry, '.css')) {
        $tags[] = '<link rel="stylesheet" href="' . $url . '">';
      } else {
        $tags[] = '<script type="module" src="' . $url . '"></script>';
      }
    }
    return implode("\n", $tags);
  }
}
