#!/bin/bash
# Script untuk cek status email queue

echo "==================================="
echo "STATUS EMAIL QUEUE - KOMBI SYSTEM"
echo "==================================="
echo ""

docker exec kbs_web php -r "
require_once '/app/src/config/init.php';
\$db = (new Database())->getConnection();

// Total count
\$stmt = \$db->query('SELECT status, COUNT(*) as count FROM email_queue GROUP BY status');
echo \"Status Email:\n\";
echo \"-----------------------------------\n\";
while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
    \$icon = \$row['status'] == 'SENT' ? '✅' : (\$row['status'] == 'FAILED' ? '❌' : '⏳');
    echo \" \$icon {\$row['status']}: {\$row['count']} email\n\";
}
echo \"-----------------------------------\n\";

// Latest 5 emails
echo \"\n5 Email Terakhir:\n\";
echo \"-----------------------------------\n\";
\$stmt = \$db->query('SELECT id, to_email, subject, status, sent_at FROM email_queue ORDER BY created_at DESC LIMIT 5');
while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
    \$icon = \$row['status'] == 'SENT' ? '✅' : (\$row['status'] == 'FAILED' ? '❌' : '⏳');
    \$sent = \$row['sent_at'] ? \"-> {\$row['sent_at']}\" : '';
    echo \" \$icon [#{\$row['id']}] {\$row['to_email']} - {\$row['subject']} \$sent\n\";
}
echo \"-----------------------------------\n\";
"

echo ""
echo "Untuk detail lebih lanjut, cek log:"
echo "  docker exec kbs_web tail -50 /app/storage/logs/email_debug.log"
