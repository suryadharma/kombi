#!/bin/bash
# Script untuk testing email dosen

echo "==================================="
echo "TESTING EMAIL DOSEN"
echo "==================================="
echo ""

# Cek dosen yang punya email
echo "1. Dosen dengan email:"
echo "-----------------------------------"
docker exec kbs_web php -r "
require_once '/app/src/config/init.php';
\$db = (new Database())->getConnection();
\$stmt = \$db->query('SELECT id, name, email FROM users WHERE email IS NOT NULL AND email != \"\" AND role LIKE \"%dosen%\"');
while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
    echo \"  [{\$row['id']}] {\$row['name']} - {\$row['email']}\n\";
}
"
echo ""

# Cek email queue terakhir
echo "2. Email queue terakhir:"
echo "-----------------------------------"
docker exec kbs_web php -r "
require_once '/app/src/config/init.php';
\$db = (new Database())->getConnection();
\$stmt = \$db->query('SELECT id, to_email, subject, status, sent_at FROM email_queue ORDER BY created_at DESC LIMIT 5');
while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
    \$icon = \$row['status'] == 'SENT' ? '✅' : (\$row['status'] == 'FAILED' ? '❌' : '⏳');
    \$sent = \$row['sent_at'] ? \"-> {\$row['sent_at']}\" : '';
    echo \"  \$icon [#{\$row['id']}] {\$row['to_email']} - {\$row['subject']} \$sent\n\";
}
"
echo ""

echo "3. Instruksi Testing:"
echo "-----------------------------------"
echo "  a. Login sebagai dosen yang punya email"
echo "  b. Buka halaman penilaian mahasiswa"
echo "  c. Isi dan submit nilai"
echo "  d. Cek inbox email dosen"
echo "  e. Jalankan script ini lagi untuk verifikasi"
echo ""
echo "4. Cek log real-time:"
echo "-----------------------------------"
echo "  docker exec kbs_web tail -f /app/storage/logs/email_debug.log"
echo ""
