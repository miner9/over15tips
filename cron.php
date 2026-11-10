<?php
// Τρέξε από cron κάθε 3 ώρες:  php /path/to/cron.php
// ώστε οι επισκέπτες να μη περιμένουν ποτέ την ανανέωση.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__ . '/lib.php';
echo refresh_cache() ? "OK\n" : "FAIL\n";
