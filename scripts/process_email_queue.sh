#!/bin/bash
# Script untuk memproses email queue secara manual
# Gunakan ini jika cron job belum disetup

echo "Processing email queue..."
docker exec kbs_web php -r "
require_once '/app/src/config/init.php';
require_once '/app/src/services/EmailService.php';

\$db = (new Database())->getConnection();
\$emailService = new EmailService(\$db);

\$result = \$emailService->processQueue(10);

echo \"✅ Processed: {\$result['processed']}\n\";
echo \"✅ Sent: {\$result['sent']}\n\";
echo \"❌ Failed: {\$result['failed']}\n\";

if (\$result['failed'] > 0) {
    echo \"\nErrors:\n\";
    print_r(\$result['errors']);
}
"

echo ""
echo "Cek status: ./scripts/check_email_queue.sh"
