<?php

namespace App\Dto;

class CommentDto
{
    public function __construct(
        public string $name,
        public string $tmp_name,
        public int $size,
        public string $type,
    ) {
    }
}
