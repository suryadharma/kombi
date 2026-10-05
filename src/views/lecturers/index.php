<?php
$lecturers = $lecturers ?? [];
$search = $search ?? '';
$assignmentsByLecturer = $assignmentsByLecturer ?? [];
?>

<div class="row">
    <div class="col-12">
        <h2>Daftar Dosen</h2>
        <p>Kelola data dosen program studi</p>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6 d-flex flex-wrap gap-2">
        <a href="/lecturers/create" class="btn btn-primary">Tambah Dosen</a>
        <a href="/lecturers/import" class="btn btn-success">Impor dari CSV</a>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <form method="GET" action="/lecturers" class="d-inline">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari dosen..." name="search" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Daftar Dosen</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="lecturersTable">
                        <thead>
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Prodi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lecturers)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data dosen</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($lecturers as $lecturer): ?>
                            <tr>
                                <td><?= htmlspecialchars($lecturer['nip'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($lecturer['name'] ?? '-') ?></td>
                                <td><?= !empty($lecturer['prodi']) ? htmlspecialchars($lecturer['prodi']) : '-' ?></td>
                                <td>
                                    <?php if (!empty($lecturer['is_external'])): ?>
                                    <span class="badge bg-warning text-dark">Eksternal</span>
                                    <?php else: ?>
                                    <span class="badge bg-success">Internal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-flex flex-wrap gap-1">
                                    <?php
                                    $assignmentSet = $assignmentsByLecturer[$lecturer['id']] ?? ['pembimbing' => [], 'penguji' => []];
                                    $payload = [
                                        'lecturer' => [
                                            'name' => $lecturer['name'] ?? '',
                                            'nip' => $lecturer['nip'] ?? '',
                                            'prodi' => $lecturer['prodi'] ?? ''
                                        ],
                                        'pembimbing' => $assignmentSet['pembimbing'] ?? [],
                                        'penguji' => $assignmentSet['penguji'] ?? []
                                    ];
                                    $payloadJson = htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                                    ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#lecturerAssignmentModal"
                                            data-assignments="<?= $payloadJson ?>">
                                        Mahasiswa
                                    </button>
                                    <a href="/lecturers/<?= $lecturer['id'] ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                                    <?php if (!empty($lecturer['user_id'])): ?>
                                    <a href="/users/reset-password?type=dosen&amp;user_id=<?= (int)$lecturer['user_id'] ?>"
                                       class="btn btn-sm btn-outline-warning"
                                       title="Reset Password Dosen">
                                        <i class="fas fa-key"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="/lecturers/<?= $lecturer['id'] ?>/delete" class="btn btn-sm btn-danger"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus dosen ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lecturerAssignmentModal" tabindex="-1" aria-labelledby="lecturerAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="lecturerAssignmentModalLabel">Mahasiswa Binaan &amp; Ujian</h5>
                    <div class="text-muted small" id="lecturerAssignmentMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-semibold">Sebagai Pembimbing</h6>
                        <ul class="list-group" id="lecturerPembimbingList"></ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semibold">Sebagai Penguji</h6>
                        <ul class="list-group" id="lecturerPengujiList"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lecturerAssignmentModal = document.getElementById('lecturerAssignmentModal');
    if (lecturerAssignmentModal) {
        lecturerAssignmentModal.addEventListener('show.bs.modal', function(event) {
            const trigger = event.relatedTarget;
            const raw = trigger ? trigger.getAttribute('data-assignments') : null;
            let data = null;
            if (raw) {
                try {
                    data = JSON.parse(raw);
                } catch (error) {
                    data = null;
                }
            }

            const modalTitle = lecturerAssignmentModal.querySelector('#lecturerAssignmentModalLabel');
            const metaEl = lecturerAssignmentModal.querySelector('#lecturerAssignmentMeta');
            const pembimbingList = lecturerAssignmentModal.querySelector('#lecturerPembimbingList');
            const pengujiList = lecturerAssignmentModal.querySelector('#lecturerPengujiList');

            const lecturerInfo = data && data.lecturer ? data.lecturer : {};
            if (modalTitle) {
                const name = lecturerInfo.name || 'Dosen';
                modalTitle.textContent = `Mahasiswa Binaan & Ujian - ${name}`;
            }
            if (metaEl) {
                const metaParts = [];
                if (lecturerInfo.nip) {
                    metaParts.push(`NIP ${lecturerInfo.nip}`);
                }
                if (lecturerInfo.prodi) {
                    metaParts.push(lecturerInfo.prodi);
                }
                metaEl.textContent = metaParts.join(' · ');
            }

            const fillList = function(container, items, emptyMessage) {
                if (!container) {
                    return;
                }
                container.innerHTML = '';
                if (!items || !items.length) {
                    const emptyItem = document.createElement('li');
                    emptyItem.className = 'list-group-item text-muted';
                    emptyItem.textContent = emptyMessage;
                    container.appendChild(emptyItem);
                    return;
                }
                items.forEach(function(item) {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';

                    const roleEl = document.createElement('div');
                    roleEl.className = 'fw-semibold';
                    roleEl.textContent = item.role || '-';
                    li.appendChild(roleEl);

                    const nameEl = document.createElement('div');
                    nameEl.className = 'text-muted small';
                    const studentName = item.student_name || '-';
                    const nim = item.nim ? ` (${item.nim})` : '';
                    const angkatan = item.angkatan ? ` · Angkatan ${item.angkatan}` : '';
                    nameEl.textContent = `${studentName}${nim}${angkatan}`;
                    li.appendChild(nameEl);

                    if (item.letter_link) {
                        const linkEl = document.createElement('a');
                        linkEl.href = item.letter_link;
                        linkEl.target = '_blank';
                        linkEl.rel = 'noopener';
                        linkEl.className = 'small d-inline-flex align-items-center gap-1 mt-1';
                        linkEl.innerHTML = '<i class="fas fa-external-link-alt"></i> Surat Tugas';
                        li.appendChild(linkEl);
                    }

                    container.appendChild(li);
                });
            };

            fillList(
                pembimbingList,
                data && data.pembimbing ? data.pembimbing : [],
                'Belum ada mahasiswa bimbingan.'
            );
            fillList(
                pengujiList,
                data && data.penguji ? data.penguji : [],
                'Belum ada mahasiswa yang diuji.'
            );
        });
    }
});
</script>
