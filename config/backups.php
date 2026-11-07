<?php

return [
    'destination' => env('BACKUP_PATH', dirname(base_path()).DIRECTORY_SEPARATOR.'siak-intel-backups'),
    'mysqldump' => env('MYSQLDUMP_BINARY', 'C:\\xampp62\\mysql\\bin\\mysqldump.exe'),
];
