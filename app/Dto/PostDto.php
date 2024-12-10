<?php

namespace App\Dto;

class PostDto
{
    public function __construct(
        public string $name,
        public string $tmp_name,
        public int $size,
        public string $type,
    ) {
    }
}
