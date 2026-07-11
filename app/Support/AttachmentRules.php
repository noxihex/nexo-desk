<?php

namespace App\Support;

final class AttachmentRules
{
    public const MAX_FILES = 5;
    public const MAX_SIZE_KILOBYTES = 10240;
    public const EXTENSIONS = 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt,mp4,kmz,kml,zip';

    public static function for(string $field): array
    {
        return [
            $field => ['nullable', 'array', 'max:' . self::MAX_FILES],
            $field . '.*' => ['file', 'max:' . self::MAX_SIZE_KILOBYTES, 'mimes:' . self::EXTENSIONS],
        ];
    }
}
