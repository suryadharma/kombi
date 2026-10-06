<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card mb-4">
            <div class="card-header text-center">
                <h3><i class="fas fa-leaf"></i> <?= htmlspecialchars(AppSettings::getName()) ?></h3>
                <p class="text-muted"><?= htmlspecialchars(AppSettings::getFullName()) ?></p>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="/auth/login">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card border-info shadow-sm">
            <div class="card-header bg-info text-white">
                <strong><i class="fas fa-user-shield me-2"></i>Penguji Eksternal?</strong>
            </div>
            <div class="card-body text-center">
                <p class="mb-2 text-muted">Gunakan <span class="font-monospace">NIP/NIDN + Token</span></p>
                <a href="/external/login" class="btn btn-outline-info w-100">
                    <i class="fas fa-door-open me-1"></i> Portal Khusus
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-5 offset-lg-1 mt-4 mt-lg-0">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Jadwal Terdekat</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingEvents)): ?>
                <!-- Empty state when no upcoming events -->
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                    <h5 class="text-muted">Tidak Ada Jadwal Mendatang</h5>
                    <p class="text-muted">Belum ada jadwal seminar atau ujian skripsi yang dijadwalkan.</p>
                </div>
                <?php else: ?>
                <!-- Tabs for filtering schedule types -->
                <ul class="nav nav-tabs mb-3" id="scheduleTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-schedules" type="button" role="tab">Semua</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sempo-tab" data-bs-toggle="tab" data-bs-target="#sempo-schedules" type="button" role="tab">SEMPRO</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="semhas-tab" data-bs-toggle="tab" data-bs-target="#semhas-schedules" type="button" role="tab">SEMHAS</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sidang-tab" data-bs-toggle="tab" data-bs-target="#sidang-schedules" type="button" role="tab">Sidang</button>
                    </li>
                </ul>
                
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="all-schedules" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama</th>
                                        <th>Tahap</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingEvents as $event): ?>
                                        <tr>
                                            <td>
                                                <small class="fw-bold d-block"><?= htmlspecialchars($event['nim']) ?></small>
                                                <small class="text-muted"><?= htmlspecialchars($event['student_name']) ?></small>
                                                
                                                <?php if (!empty($event['lecturers']['pembimbing'])): ?>
                                                    <div class="mt-1">
                                                        <small class="text-info">
                                                            <strong>Pembimbing:</strong><br>
                                                            <?php foreach ($event['lecturers']['pembimbing'] as $pembimbing): ?>
                                                                <span class="badge bg-primary"><?= htmlspecialchars($pembimbing) ?></span>
                                                            <?php endforeach; ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($event['lecturers']['penguji'])): ?>
                                                    <div class="mt-1">
                                                        <small class="text-warning">
                                                            <strong>Penguji:</strong><br>
                                                            <?php foreach ($event['lecturers']['penguji'] as $penguji): ?>
                                                                <span class="badge bg-secondary"><?= htmlspecialchars($penguji) ?></span>
                                                            <?php endforeach; ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php if($event['type'] === 'SEMPRO'): ?>bg-primary
                                                    <?php elseif($event['type'] === 'SEMHAS'): ?>bg-warning text-dark
                                                    <?php elseif($event['type'] === 'UJIAN_SKRIPSI'): ?>bg-danger
                                                    <?php else: ?>bg-secondary<?php endif; ?>">
                                                    <?= htmlspecialchars($event['type_label']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold"><?= htmlspecialchars($event['date_formatted']) ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($event['time_formatted']) ?></small>
                                                    <small class="text-success fw-medium"><?= htmlspecialchars($event['days_until']) ?></small>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Tab content for each schedule type will be dynamically generated by JavaScript -->
                </div>
            </div>
            <?php endif; ?>
            <div class="card-footer text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                <?php if (empty($upcomingEvents)): ?>
                Tidak ada jadwal yang tersedia saat ini.
                <?php else: ?>
                <?= count($upcomingEvents) ?> Jadwal mendatang. Login untuk mengelola jadwal.
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Group events by type
    const events = <?php echo json_encode($upcomingEvents); ?>;
    
    // Create filtered tables for each type
    const eventTypes = {
        'sempo-schedules': 'SEMPRO',
        'semhas-schedules': 'SEMHAS',
        'sidang-schedules': 'UJIAN_SKRIPSI'
    };
    
    Object.keys(eventTypes).forEach(typeKey => {
        const eventType = eventTypes[typeKey];
        const filteredEvents = events.filter(event => event.type === eventType);
        
        if (filteredEvents.length > 0) {
            const tabPane = document.createElement('div');
            tabPane.className = 'tab-pane fade';
            tabPane.id = typeKey;
            tabPane.setAttribute('role', 'tabpanel');
            
            let tableHtml = `
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Tahap</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            filteredEvents.forEach(event => {
                let pembimbingHtml = '';
                if (event.lecturers.pembimbing && event.lecturers.pembimbing.length > 0) {
                    pembimbingHtml = '<div class="mt-1"><small class="text-info"><strong>Pembimbing:</strong><br>';
                    event.lecturers.pembimbing.forEach(pembimbing => {
                        pembimbingHtml += `<span class="badge bg-primary">${pembimbing}</span>`;
                    });
                    pembimbingHtml += '</small></div>';
                }
                
                let pengujiHtml = '';
                if (event.lecturers.penguji && event.lecturers.penguji.length > 0) {
                    pengujiHtml = '<div class="mt-1"><small class="text-warning"><strong>Penguji:</strong><br>';
                    event.lecturers.penguji.forEach(penguji => {
                        pengujiHtml += `<span class="badge bg-secondary">${penguji}</span>`;
                    });
                    pengujiHtml += '</small></div>';
                }
                
                tableHtml += `
                    <tr>
                        <td>
                            <small class="fw-bold d-block">${event.nim}</small>
                            <small class="text-muted">${event.student_name}</small>
                            ${pembimbingHtml}
                            ${pengujiHtml}
                        </td>
                        <td>
                            <span class="badge">${
                                event.type === 'SEMPRO' ? '<span class="badge bg-primary">SEMINAR PROPOSAL</span>' :
                                event.type === 'SEMHAS' ? '<span class="badge bg-warning text-dark">SEMINAR HASIL</span>' :
                                event.type === 'UJIAN_SKRIPSI' ? '<span class="badge bg-danger">UJIAN SKRIPSI</span>' : 
                                '<span class="badge bg-secondary">' + event.type_label + '</span>'
                            }</span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">${event.date_formatted}</span>
                                <small class="text-muted">${event.time_formatted}</small>
                                <small class="text-success fw-medium">${event.days_until}</small>
                            </div>
                        </td>
                    </tr>`;
            });
            
            tableHtml += '</tbody></table></div>';
            tabPane.innerHTML = tableHtml;
            document.querySelector('.tab-content').appendChild(tabPane);
        } else {
            // Create empty state for tabs with no events
            const tabPane = document.createElement('div');
            tabPane.className = 'tab-pane fade';
            tabPane.id = typeKey;
            tabPane.setAttribute('role', 'tabpanel');
            tabPane.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-calendar-alt fa-3x mb-3 text-muted"></i>
                    <h5 class="text-muted">Tidak Ada Jadwal</h5>
                    <p class="text-muted">Belum ada jadwal ${eventType.toLowerCase()} yang terdaftar.</p>
                </div>`;
            document.querySelector('.tab-content').appendChild(tabPane);
        }
    });
});
</script>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
