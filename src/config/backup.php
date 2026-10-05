<?php

$storagePath = getenv('BACKUP_STORAGE_PATH');
if (!$storagePath) {
    $storagePath = STORAGE_PATH . '/backups';
}

$externalTarget = null;
$externalType = strtolower((string)getenv('BACKUP_EXTERNAL_TYPE'));

if ($externalType === 'path') {
    $path = rtrim((string)getenv('BACKUP_EXTERNAL_PATH'), '/');
    if ($path !== '') {
        $externalTarget = $path;
    }
} elseif ($externalType === 'smb') {
    $host = getenv('BACKUP_SMB_HOST') ?: '';
    $share = getenv('BACKUP_SMB_SHARE') ?: '';
    if ($host !== '' && $share !== '') {
        $externalTarget = [
            'type' => 'smb',
            'host' => $host,
            'share' => $share,
            'path' => getenv('BACKUP_SMB_PATH') ?: '',
            'username' => getenv('BACKUP_SMB_USERNAME') ?: '',
            'password' => getenv('BACKUP_SMB_PASSWORD') ?: (getenv('KOMBI_BACKUP_SMB_PASSWORD') ?: 'but01j0'),
            'binary' => getenv('BACKUP_SMB_BINARY') ?: '/usr/bin/smbclient',
        ];

        $workgroup = getenv('BACKUP_SMB_WORKGROUP');
        if ($workgroup) {
            $externalTarget['workgroup'] = $workgroup;
        }
    }
}

$retentionDays = getenv('BACKUP_RETENTION_DAYS');
if ($retentionDays === false || $retentionDays === null || trim($retentionDays) === '') {
    $retentionDays = 30;
} else {
    $retentionDays = (int)$retentionDays;
}

return [
    'storage_path' => $storagePath,
    'external_target' => $externalTarget,
    'retention_days' => $retentionDays,
];
