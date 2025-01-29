<?php

namespace App\Dto;

/**
 * Class FileDto
 * Represents a Data Transfer Object (DTO) for a file uploaded via a form.
 */
class FileDto
{
    /**
     * @param string $name     The original name of the uploaded file.
     * @param string $tmp_name The temporary path where the file is stored on the server.
     * @param int    $size     The size of the uploaded file in bytes.
     * @param string $type     The MIME type of the uploaded file.
     */
    public function __construct(
        public string $name,
        public string $tmp_name,
        public int $size,
        public string $type,
    ) {
    }
}
