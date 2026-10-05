<?php
$activeTab = $activeTab ?? 'study';
$currentRole = $_SESSION['role'] ?? 'guest';
$tabs = [
    'study' => ['label' => 'Masa Studi', 'url' => '/reports'],
    'tracking' => ['label' => 'Pelacakan Tugas Akhir', 'url' => '/reports/tracking'],
    'graduated' => ['label' => 'Data Lulusan', 'url' => '/reports/graduated'],
    'workload' => ['label' => 'Beban Dosen', 'url' => '/reports/workload'],
    'historical_workload' => ['label' => 'Beban Dosen Historis', 'url' => '/reports/historical-workload'],
    'sla' => ['label' => 'SLA Entri Nilai', 'url' => '/reports/sla', 'visible' => ($currentRole === 'superadmin')],
    'bypass' => ['label' => 'Riwayat Bypass', 'url' => '/reports/bypass'],
];
?>
<ul class="nav nav-pills mb-4">
    <?php foreach ($tabs as $key => $tab):
        // Skip tabs that are not visible (for superadmin-only tabs)
        if (isset($tab['visible']) && !$tab['visible']) {
            continue;
        }
    ?>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === $key ? 'active' : '' ?>" href="<?= $tab['url'] ?>">
            <?= htmlspecialchars($tab['label']) ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>
