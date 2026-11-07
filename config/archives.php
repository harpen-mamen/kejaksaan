<?php

return [
    'max_kb' => (int) env('ARCHIVE_MAX_KB', 10240),
    'mime_types' => [
        'application/pdf',
        'text/plain',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
    ],
    'extensions_by_mime' => [
        'application/pdf' => ['pdf'],
        'text/plain' => ['txt'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
    ],
];
