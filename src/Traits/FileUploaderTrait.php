<?php

use Exception;

trait FileUploaderTrait
{
    protected ?string $fileBase64 = null;
    protected ?string $fileMimeType = null;

    /**
     * Fetch a file from a URL and encode it as Base64.
     *
     * @param string $url
     * @return $this
     * @throws Exception
     */
    public function fromUrl(string $url): self
    {
        $fileContent = file_get_contents($url);

        if ($fileContent === false) {
            throw new Exception("Unable to fetch the file from {$url}");
        }

        $this->fileBase64 = base64_encode($fileContent);
        $this->fileMimeType = $this->determineMimeType($url);

        return $this;
    }

   

    /**
     * Build the payload for the file.
     *
     * @return array
     * @throws Exception
     */
    public function buildPayload(): array
    {
        if (!$this->fileBase64 || !$this->fileMimeType) {
            throw new Exception('File data is incomplete. Ensure the file is loaded and MIME type is determined.');
        }

        $source = [
            'type' => 'base64',
            'media_type' => $this->fileMimeType,
            'data' => $this->fileBase64,
        ];

        $payload = [
            'type' => 'document',
            'source' => $source,
        ];

        return $payload;
    }

    /**
     * Determine the MIME type of the file based on the URL.
     *
     * @param string $url
     * @return string
     */
    protected function determineMimeType(string $url): string
    {
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'json' => 'application/json',
            // Add more extensions and MIME types as needed
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}

