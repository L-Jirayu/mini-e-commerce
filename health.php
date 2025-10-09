<?php
// ไม่มี include อะไรทั้งนั้น ไม่เรียก db.php
http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo "ok";
