<?php

// Define application routes
$routes = [
    // Auth routes
    '/' => 'AuthController@login',
    '/login' => 'AuthController@login',
    '/auth/login' => 'AuthController@loginProcess',
    '/logout' => 'AuthController@logout',
    '/auth/logout' => 'AuthController@logout',
    '/auth/change-password' => 'AuthController@changePassword',
    '/auth/skip-change-password' => 'AuthController@skipChangePassword',

    // Student routes
    '/students' => 'StudentController@index',
    '/students/data' => 'StudentController@listData',
    '/students/create' => 'StudentController@create',
    '/students/store' => 'StudentController@store',
    '/students/import' => 'StudentImportController@importForm',
    '/students/bulk-delete' => 'StudentController@bulkDelete',
    '/students/progress' => 'StudentController@progress',
    '/students/assignment-status' => 'StudentController@assignmentStatus',
    '/students/([^/]+)' => 'StudentController@show',
    '/students/([^/]+)/edit' => 'StudentController@edit',
    '/students/([^/]+)/update' => 'StudentController@update',
    '/students/([^/]+)/delete' => 'StudentController@delete',
    '/profile' => 'ProfileController@index',
    '/profile/update' => 'ProfileController@update',

    // Lecturer routes
    '/lecturers' => 'LecturerController@index',
    '/lecturers/create' => 'LecturerController@create',
    '/lecturers/store' => 'LecturerController@store',
    '/lecturers/([^/]+)/edit' => 'LecturerController@edit',
    '/lecturers/([^/]+)/update' => 'LecturerController@update',
    '/lecturers/([^/]+)/delete' => 'LecturerController@delete',
    '/lecturers/import' => 'LecturerController@importForm',

    // Component routes
    '/components' => 'ComponentController@index',
    '/components/create' => 'ComponentController@create',
    '/components/([^/]+)/edit' => 'ComponentController@edit',
    '/components/([^/]+)/delete' => 'ComponentController@delete',

    // Evaluation routes
    '/evaluations/form/([^/]+)/([^/]+)' => 'EvaluationController@showForm',
    '/evaluations/bypass/([^/]+)/([^/]+)' => 'EvaluationController@bypassForm',
    '/evaluations/([^/]+)/toggle-lock' => 'EvaluationController@toggleLock',

    // User routes
    '/users' => 'UserController@index',
    '/users/create' => 'UserController@create',
    '/users/([^/]+)/edit' => 'UserController@edit',
    '/users/([^/]+)/delete' => 'UserController@delete',
    '/users/reset-password' => 'UserController@resetPasswordForm',
    '/users/get-users' => 'UserController@getUsersByType',
    '/users/roles' => 'UserController@manageRoles',

    // Title routes
    '/titles/submit' => 'TitleController@submit',
    '/titles/verify' => 'TitleController@verify',
    '/titles/verify/data' => 'TitleController@verifyData',
    '/titles/verify/([^/]+)' => 'TitleController@verifyDetail',
    '/titles/view' => 'TitleController@view',
    '/titles/view/data' => 'TitleController@viewData',
    '/titles/view/([^/]+)' => 'TitleController@viewDetail',
    '/titles/([^/]+)/edit' => 'TitleController@edit',
    '/titles/([^/]+)/update' => 'TitleController@update',
    '/titles/([^/]+)/delete' => 'TitleController@delete',

    // Assignment routes
    '/assignments/set' => 'AssignmentController@set',
    '/assignments/history' => 'AssignmentController@history',
    '/assignments/history/([^/]+)' => 'AssignmentController@history',
    '/assignments/change/([^/]+)' => 'AssignmentController@change',
    '/assignments/cancel' => 'AssignmentController@cancel',

    // Event routes
    '/events' => 'EventController@index',
    '/events/create' => 'EventController@create',
    '/events/([^/]+)/edit' => 'EventController@edit',
    '/events/check-progress' => 'EventController@checkProgress',
    '/events/get-lecturer-assignments' => 'EventController@getLecturerAssignments',
    '/events/check-schedule-conflict' => 'EventController@checkScheduleConflict',

    // Score routes
    '/scores/submit' => 'ScoreController@submit',
    '/scores/recap' => 'ScoreController@recap',
    '/scores/recap/data' => 'ScoreController@recapData',
    '/scores/lecturer-recap' => 'ScoreController@lecturerRecap',
    '/scores/lecturer-recap/data' => 'ScoreController@lecturerRecapData',
    '/scores/export/([^/]+)/final' => 'ScoreController@exportFinalThesisScore',
    '/scores/export/([^/]+)/([^/]+)' => 'ScoreController@exportStage',
    '/scores/panel/finalize' => 'ScoreController@finalize',

    // External examiner routes
    '/external/invite' => 'ExternalController@invite',
    '/external/login' => 'ExternalController@login',
    '/external/score/submit' => 'ExternalController@submitScore',
    '/external/logout' => 'ExternalController@logout',

    // Verification routes
    '/verification/evaluation' => 'VerificationController@evaluation',
    '/verification/final' => 'VerificationController@finalScore',

    // Student timeline
    '/timeline' => 'TimelineController@index',
    '/timeline/scores/([^/]+)' => 'TimelineController@scoreDetail',
    '/debug-email' => 'DebugEmailController@index',

    // Maintenance routes
    '/cleanup-test-data' => 'MaintenanceController@cleanupTestData',

    // Report routes
    '/reports/study-period' => 'ReportController@studyPeriod',
    '/reports/study-period/data' => 'ReportController@studyPeriodData',
    '/reports/tracking' => 'ReportController@tracking',
    '/reports/tracking/data' => 'ReportController@trackingData',
    '/reports/graduated/([^/]+)/export/([^/]+)' => 'ReportController@exportGraduatedStage',
    '/reports/graduated' => 'ReportController@graduated',
    '/reports/graduated/data' => 'ReportController@graduatedData',
    '/reports/workload' => 'ReportController@workload',
    '/reports/workload/data' => 'ReportController@workloadData',
    '/reports/historical-workload' => 'ReportController@historicalWorkload',
    '/reports/historical-workload/data' => 'ReportController@historicalWorkloadData',
    '/reports/historical-workload-details' => 'ReportController@historicalWorkloadDetails',
    '/reports/sla' => 'ReportController@sla',
    '/reports/sla/data' => 'ReportController@slaData',
    '/reports/bypass' => 'ReportController@bypassReport',
    '/reports/bypass/data' => 'ReportController@bypassData',
    '/reports/duration' => 'ReportController@studyPeriod',
    '/reports/summary' => 'ReportController@summary',
    '/reports/summary/data' => 'ReportController@summaryData',
    '/reports/test' => 'ReportController@testData',
    '/reports' => 'ReportController@studyPeriod',

    // Settings routes
    '/settings' => 'SettingsController@index',
    '/settings/backup' => 'BackupSettingsController@index',
    '/settings/test-email' => 'SettingsController@testEmail',

    // API routes
    '/api/csrf/token' => 'ApiCsrfController@token',

    // Backup routes
    '/backups' => 'BackupController@index',
    '/backups/download/([^/]+)' => 'BackupController@download',

    // Home routes
    '/dashboard' => 'HomeController@index',
    '/debug-roles' => 'HomeController@debugRoles',
    '/test-controller' => 'TestController@index',
    '/test-email-queue' => 'TestController@testEmail',

];

// Add POST routes separately to avoid conflicts
$postRoutes = [
    '/auth/login' => 'AuthController@loginProcess',
    '/auth/change-password' => 'AuthController@changePasswordProcess',
    '/auth/switch-role' => 'AuthController@switchRole',
    '/profile/update' => 'ProfileController@update',
    '/settings/update' => 'SettingsController@update',
    '/settings/backup' => 'BackupSettingsController@save',
    '/settings/test-email' => 'SettingsController@testEmail',
    '/settings/backup/test-smb' => 'BackupSettingsController@testSmb',
    '/settings/backup/test-ftp' => 'BackupSettingsController@testFtp',
    '/settings/diagnose-email' => 'SettingsController@diagnoseEmail',
    '/students/store' => 'StudentController@store',
    '/students/([^/]+)/update' => 'StudentController@update',
    '/students/([^/]+)/delete' => 'StudentController@delete',
    '/students/bulk-delete' => 'StudentController@bulkDeleteProcess',
    '/students/import/process' => 'StudentImportController@importProcess',
    '/students' => 'StudentController@store',
    '/lecturers/store' => 'LecturerController@store',
    '/lecturers/([^/]+)/update' => 'LecturerController@update',
    '/lecturers/([^/]+)/delete' => 'LecturerController@delete',
    '/lecturers/import/process' => 'LecturerController@importProcess',
    '/components/store' => 'ComponentController@store',
    '/components/([^/]+)/update' => 'ComponentController@update',
    '/components/([^/]+)/delete' => 'ComponentController@delete',
    '/users/store' => 'UserController@store',
    '/users/([^/]+)/update' => 'UserController@update',
    '/users/([^/]+)/delete' => 'UserController@delete',
    '/users/reset-password' => 'UserController@resetPasswordProcess',
    '/users/roles/add' => 'UserController@addRole',
    '/users/roles/remove' => 'UserController@removeRole',
    '/titles/submit' => 'TitleController@submit',
    '/titles/verify/([^/]+)/process' => 'TitleController@processVerification',
    '/titles/([^/]+)/update' => 'TitleController@update',
    '/titles/([^/]+)/delete' => 'TitleController@delete',
    '/assignments/set' => 'AssignmentController@set',
    '/assignments/change/([^/]+)' => 'AssignmentController@change',
    '/assignments/cancel' => 'AssignmentController@cancel',
    '/events/store' => 'EventController@store',
    '/events/([^/]+)/update' => 'EventController@update',
    '/events/([^/]+)/delete' => 'EventController@delete',
    '/scores/submit' => 'ScoreController@submit',
    '/scores/panel/finalize' => 'ScoreController@finalize',
    '/external/login' => 'ExternalController@login',
    '/external/score/submit' => 'ExternalController@submitScore',
    '/evaluations/form/([^/]+)/([^/]+)/save' => 'EvaluationController@saveEvaluation',
    '/evaluations/bypass/([^/]+)/([^/]+)/save' => 'EvaluationController@saveBypass',
    '/email-queue/process' => 'EmailQueueController@process',
    '/email-queue/status' => 'EmailQueueController@status',
    '/backups/create' => 'BackupController@create',
    '/backups/restore' => 'BackupController@restore',
    '/backups/delete' => 'BackupController@delete',
    '/backups/sync' => 'BackupController@sync',
];

return ['routes' => $routes, 'postRoutes' => $postRoutes];
