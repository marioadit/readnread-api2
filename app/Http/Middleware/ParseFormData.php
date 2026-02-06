<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParseFormData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $contentType = $request->header('Content-Type');

            if (strpos($contentType, 'multipart/form-data') !== false) {
                $rawInput = file_get_contents('php://input');

                if (!empty($rawInput)) {
                    $boundary = $this->getBoundary($contentType);

                    if ($boundary) {
                        $parsedData = $this->parseMultipartData($rawInput, $boundary);

                        // Merge parsed data into request
                        $request->merge($parsedData['fields']);

                        // Handle files
                        if (!empty($parsedData['files'])) {
                            foreach ($parsedData['files'] as $key => $file) {
                                $request->files->set($key, $file);
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }

    private function getBoundary($contentType)
    {
        if (preg_match('/boundary=(.*)$/', $contentType, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function parseMultipartData($rawInput, $boundary)
    {
        $fields = [];
        $files = [];

        // Split by boundary
        $parts = preg_split('/-+' . preg_quote($boundary, '/') . '/', $rawInput);

        foreach ($parts as $part) {
            if (empty(trim($part)) || trim($part) == '--') {
                continue;
            }

            // Split headers and content
            $headerEndPos = strpos($part, "\r\n\r\n");
            if ($headerEndPos === false) {
                continue;
            }

            $headers = substr($part, 0, $headerEndPos);
            $content = substr($part, $headerEndPos + 4);

            // Remove trailing CRLF
            $content = rtrim($content, "\r\n");

            // Parse Content-Disposition header
            if (preg_match('/Content-Disposition: form-data; name="([^"]+)"(?:; filename="([^"]+)")?/', $headers, $matches)) {
                $fieldName = $matches[1];
                $filename = isset($matches[2]) ? $matches[2] : null;

                if ($filename) {
                    // It's a file
                    $contentType = 'application/octet-stream';
                    if (preg_match('/Content-Type: (.+)/', $headers, $typeMatches)) {
                        $contentType = trim($typeMatches[1]);
                    }

                    // Create temporary file
                    $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                    file_put_contents($tempFile, $content);

                    $files[$fieldName] = new \Illuminate\Http\UploadedFile(
                        $tempFile,
                        $filename,
                        $contentType,
                        null, // Let Laravel determine the error
                        true  // Mark as test file (this is important)
                    );
                } else {
                    // It's a regular field
                    $fields[$fieldName] = $content;
                }
            }
        }

        return [
            'fields' => $fields,
            'files' => $files
        ];
    }
}
