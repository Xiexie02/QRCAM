<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#09090b">
    <title>QRCAMv2 — Attendance</title>
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="stylesheet" href="styles.css?v=qrcam_adviser_v1">
    <script defer src="assets/qrcode-browser.js"></script>
    <script defer src="assets/html5-qrcode.min.js"></script>
    <script defer src="app.js?v=qrcam_adviser_v1"></script>
</head>
<body>
    <main id="login-view" class="login-wrap" hidden>
        <section class="login-card">
            <div class="brand-logo-wrap">
                <img class="brand-logo theme-logo-light" src="assets/logo.png" alt="QRCAM" width="58" height="58">
                <img class="brand-logo theme-logo-dark" src="assets/logo-white.png" alt="QRCAM" width="58" height="58">
            </div>
            <p class="eyebrow">ATTENDANCE MANAGEMENT</p>
            <h1>Welcome back</h1>
            <p class="muted">Sign in to manage your class attendance.</p>
            <div class="login-scan-qr-wrap">
                <a class="button secondary button-sm scan-qr-button" href="checkin.php">
                    <svg class="qr-btn-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span>Scan QR</span>
                </a>
            </div>
            <form id="login-form">
                <label>Teacher username or admin login<input name="username" autocomplete="username" required></label>
                <label>Password
                    <div class="password-field-wrap">
                        <input name="password" type="password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle-btn" aria-label="Show password" title="Show password" tabindex="-1">
                            <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </label>
                <button class="button primary wide" type="submit">Sign in</button>
                <p id="login-error" class="error-text" role="alert"></p>
            </form>
        </section>
    </main>

    <div id="app-view" class="app-shell" hidden>
        <aside class="sidebar">
            <button id="brand-toggle" class="brand" type="button" aria-label="Toggle navigation" aria-expanded="true" aria-controls="primary-navigation"><span class="brand-mark small"><img class="brand-logo-icon theme-logo-dark" src="assets/logo-white.png" alt="QRCAM" width="22" height="22"><img class="brand-logo-icon theme-logo-light" src="assets/logo.png" alt="QRCAM" width="22" height="22"></span><span class="brand-name">QRCAM<span class="brand-light">v2</span></span><span class="brand-chevron" aria-hidden="true">‹</span></button>
            <p class="side-label">WORKSPACE</p>
            <nav id="primary-navigation">
                <button class="nav-link active" data-screen="dashboard" title="Overview"><span aria-hidden="true">▦</span><span class="nav-text">Overview</span></button>
                <button class="nav-link" data-screen="students" title="Students"><span aria-hidden="true">♙</span><span class="nav-text">Students</span></button>
                <button class="nav-link" data-screen="reports" title="Reports"><span aria-hidden="true">▤</span><span class="nav-text">Reports</span></button>
                <button class="nav-link" data-screen="settings" title="Settings"><span aria-hidden="true">⚙</span><span class="nav-text">Settings</span></button>
                <button id="logout-button" class="nav-link nav-logout" type="button" title="Log out"><span aria-hidden="true">↪</span><span class="nav-text">Log out</span></button>
            </nav>
            <div class="side-bottom">
                <div class="connection" id="connection-status" role="status"><span class="live-dot"></span><span id="connection-label">Checking connection…</span></div>
                <button id="adviser-profile-btn" class="profile profile-button" type="button" aria-haspopup="dialog" aria-label="Open adviser profile" title="Open adviser profile">
                    <div class="avatar" id="adviser-avatar"><span id="adviser-avatar-initials">A</span><img id="adviser-avatar-img" class="avatar-photo" alt="Adviser photo" hidden></div>
                    <div class="profile-name">
                        <strong id="adviser-name">Adviser</strong>
                        <small id="adviser-role"><span id="adviser-section-text">Section: Compassion</span></small>
                    </div>
                    <svg class="profile-edit-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </button>
            </div>
        </aside>

        <section class="main-area">
            <header class="topbar">
                <button id="menu-toggle" class="icon-button menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="primary-navigation">☰</button>
                <div><p class="eyebrow">CLASSROOM CONSOLE</p><h1 id="page-title">Overview</h1></div>
                <div class="topbar-actions"><span class="topbar-date"><span id="header-date"></span></span></div>
            </header>
            <div id="notice" class="notice" role="status" hidden></div>

            <section id="dashboard-screen" class="screen">
                <div class="welcome-row"><div><h2>Today's overview</h2><p class="muted">Attendance and enrolled learner totals.</p></div><button id="open-scanner" class="button primary"><span>▣</span> Scan QR code</button></div>
                <div class="stats-grid">
                    <article class="stat-card"><div class="stat-top"><span>Combined students</span><span class="stat-icon neutral" title="Combined"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span></div><strong id="total-count">0</strong><small id="combined-subtext">Total enrolled</small></article>
                    <article class="stat-card"><div class="stat-top"><span>Total Male</span><span class="stat-icon blue" title="Male"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10" cy="14" r="5"></circle><line x1="19" y1="5" x2="13.6" y2="10.4"></line><line x1="19" y1="5" x2="14" y2="5"></line><line x1="19" y1="5" x2="19" y2="10"></line></svg></span></div><strong id="male-count">0</strong><small>Male learners</small></article>
                    <article class="stat-card"><div class="stat-top"><span>Total Female</span><span class="stat-icon rose" title="Female"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="9" r="5"></circle><line x1="12" y1="14" x2="12" y2="21"></line><line x1="9" y1="18" x2="15" y2="18"></line></svg></span></div><strong id="female-count">0</strong><small>Female learners</small></article>
                    <article class="stat-card"><div class="stat-top"><span>Present</span><span class="stat-icon green" title="Present"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg></span></div><strong id="present-count">0</strong><small id="present-percent">0% of students</small></article>
                    <article class="stat-card"><div class="stat-top"><span>Absent</span><span class="stat-icon amber" title="Absent"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg></span></div><strong id="absent-count">0</strong><small id="absent-percent">0% of students</small></article>
                </div>
                <div class="content-grid">
                    <article class="panel attendance-panel">
                        <div class="panel-heading"><div><h3>Attendance feed</h3><p class="muted">Latest check-ins update automatically</p></div><span class="live-pill"><span class="live-dot"></span><span id="feed-refresh-state">LIVE</span></span></div>
                        <div class="table-wrap"><table><thead><tr><th>STUDENT</th><th>TIME</th><th>STATUS</th><th></th></tr></thead><tbody id="feed-rows"><tr><td colspan="4" class="empty">No students enrolled yet.</td></tr></tbody></table></div>
                    </article>
                    <article class="panel quick-panel">
                        <div class="panel-heading"><div><h3>Quick actions</h3><p class="muted">Manage your classroom</p></div></div>
                        <button class="action-tile" data-screen="students"><span class="tile-icon">♙</span><span><strong>Manage students</strong><small>View student records and QR codes</small></span><b>›</b></button>
                        <button class="action-tile" id="quick-import"><span class="tile-icon">⇧</span><span><strong>Import students</strong><small>Upload a CSV roster</small></span><b>›</b></button>
                        <button class="action-tile" data-screen="reports"><span class="tile-icon">▤</span><span><strong>Attendance reports</strong><small>Print a date-range report</small></span><b>›</b></button>
                        <input id="csv-file" type="file" accept=".csv,text/csv" hidden>
                    </article>
                </div>
                <article class="panel audit-panel">
                    <div class="panel-heading"><div><h3>Change history</h3><p class="muted">Latest adviser attendance adjustments</p></div></div>
                    <div class="table-wrap"><table><thead><tr><th>WHEN</th><th>ADVISER</th><th>CHANGE</th></tr></thead><tbody id="audit-rows"><tr><td colspan="3" class="empty">No manual changes recorded.</td></tr></tbody></table></div>
                </article>
            </section>

            <section id="students-screen" class="screen" hidden>
                <div class="welcome-row"><div><h2>Student roster</h2><p class="muted">Manage enrollment and individual attendance QR codes.</p></div><button id="students-import" class="button primary">＋ Import CSV</button></div>
                <article class="panel">
                    <div class="panel-heading roster-heading"><div><h3>Enrolled students</h3><p id="roster-count" class="muted">0 students</p></div><input id="student-search" class="search-input" type="search" placeholder="Search students…" aria-label="Search students"></div>
                    <div class="table-wrap"><table><thead><tr><th>STUDENT</th><th>STUDENT ID</th><th>SECTION</th><th>ENROLLED</th><th></th></tr></thead><tbody id="student-rows"></tbody></table></div>
                </article>
            </section>

            <section id="settings-screen" class="screen" hidden>
                <div class="welcome-row"><div><h2>Settings</h2><p class="muted">Manage this browser’s classroom defaults and dashboard refresh behavior.</p></div></div>
                <article class="panel settings-panel">
                    <div class="panel-heading"><div><h3>Classroom defaults</h3><p class="muted">Used to prefill new SF-2 reports on this device.</p></div></div>
                    <form id="settings-form" class="settings-form">
                        <label>School<input name="school" maxlength="160" required></label>
                        <label>School ID<input name="school_id" maxlength="30"></label>
                        <label>Division<input name="division" maxlength="100"></label>
                        <label>District<input name="district" maxlength="100"></label>
                        <label>Region<input name="region" maxlength="100"></label>
                        <label>School year<input name="school_year" maxlength="20" required></label>
                        <label>Semester<input name="semester" maxlength="40"></label>
                        <label>Grade level<input name="grade" maxlength="30" required></label>
                        <label>Track / strand<input name="track" maxlength="100"></label>
                        <label>Course (TVL only)<input name="course" maxlength="100"></label>
                        <label>Section<input name="section" maxlength="120"></label>
                        <label>Adviser<input name="adviser" maxlength="160"></label>
                        <label>Dashboard refresh
                            <select name="refresh_seconds">
                                <option value="3">Every 3 seconds</option>
                                <option value="10">Every 10 seconds</option>
                                <option value="30">Every 30 seconds</option>
                                <option value="0">Off (manual refresh)</option>
                            </select>
                        </label>
                        <label>Color theme
                            <select name="theme">
                                <option value="light">Light mode</option>
                                <option value="dark">Dark mode</option>
                            </select>
                        </label>
                        <p class="settings-note">Settings are stored in this browser only. They do not change your Supabase data or your adviser login.</p>
                        <div class="settings-actions"><button class="button primary" type="submit">Save settings</button><button class="button secondary" id="reset-settings" type="button">Restore defaults</button></div>
                        <p id="settings-status" class="settings-status" role="status" aria-live="polite"></p>
                    </form>
                </article>
                <article id="teacher-management" class="panel settings-panel account-management" hidden>
                    <div class="panel-heading"><div><h3>Teacher accounts</h3><p class="muted">Create and manage teacher access to this console.</p></div></div>
                    <form id="teacher-create-form" class="account-form">
                        <label>Teacher name<input name="full_name" maxlength="160" autocomplete="name" required></label>
                        <label>Username or email<input name="username" maxlength="120" autocomplete="username" required></label>
                        <label>Initial password
                            <div class="password-field-wrap">
                                <input name="password" type="password" minlength="12" maxlength="1024" autocomplete="new-password" required>
                                <button type="button" class="password-toggle-btn" aria-label="Show password" title="Show password" tabindex="-1">
                                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                        </label>
                        <button class="button primary" type="submit">Create teacher account</button>
                    </form>
                    <p class="account-help">Passwords must be at least 12 characters. Teacher accounts can update their own profile and password from Settings.</p>
                    <div class="table-wrap"><table><thead><tr><th>TEACHER</th><th>USERNAME</th><th>STATUS</th><th></th></tr></thead><tbody id="teacher-account-rows"><tr><td colspan="4" class="empty">Teacher accounts load when this page is opened.</td></tr></tbody></table></div>
                    <p id="teacher-management-status" class="settings-status" role="status" aria-live="polite"></p>
                </article>
                <article id="my-account-panel" class="panel settings-panel account-management" hidden>
                    <div class="panel-heading"><div><h3>My teacher account</h3><p class="muted">Update your name, sign-in username, or password.</p></div></div>
                    <form id="my-account-form" class="account-form">
                        <label>Teacher name<input name="full_name" maxlength="160" autocomplete="name" required></label>
                        <label>Username or email<input name="username" maxlength="120" autocomplete="username" required></label>
                        <label>Current password
                            <div class="password-field-wrap">
                                <input name="current_password" type="password" autocomplete="current-password" required>
                                <button type="button" class="password-toggle-btn" aria-label="Show password" title="Show password" tabindex="-1">
                                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                        </label>
                        <label>New password (leave blank to keep current)
                            <div class="password-field-wrap">
                                <input name="new_password" type="password" minlength="12" maxlength="1024" autocomplete="new-password">
                                <button type="button" class="password-toggle-btn" aria-label="Show password" title="Show password" tabindex="-1">
                                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                        </label>
                        <button class="button primary" type="submit">Save account</button>
                    </form>
                    <p class="account-help">Your current password is required to confirm changes. New passwords must be at least 12 characters.</p>
                    <p id="my-account-status" class="settings-status" role="status" aria-live="polite"></p>
                </article>
            </section>

            <section id="reports-screen" class="screen" hidden>
                <div class="welcome-row"><div><h2>Attendance reports</h2><p class="muted">Generate and review date-range attendance reports.</p></div></div>
                <article id="standard-report-panel" class="panel report-panel">
                    <div class="panel-heading"><div><h3>Generate report</h3><p class="muted">Select a date range to build a student attendance report.</p></div></div>
                    <form id="report-form" class="report-controls">
                        <label>Start date<input id="report-start" type="date" required></label>
                        <label>End date<input id="report-end" type="date" required></label>
                        <button class="button primary" type="submit">Generate report</button>
                    </form>
                    <div id="report-excel-view" class="report-excel-view" hidden>
                        <div class="excel-toolbar">
                            <div class="excel-toolbar-left">
                                <span class="excel-file-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </span>
                                <span id="excel-sheet-name" class="excel-sheet-tab">Attendance</span>
                            </div>
                            <div class="excel-toolbar-right">
                                <span id="report-row-count" class="excel-row-count"></span>
                                <button class="button secondary button-sm" id="print-report" type="button" disabled>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                    Print
                                </button>
                            </div>
                        </div>
                        <div class="excel-grid-wrap">
                            <table class="excel-table" id="excel-report-table">
                                <thead>
                                    <tr class="excel-header-row">
                                        <th class="excel-row-num">#</th>
                                        <th>Date</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Section</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                        <th>Note</th>
                                        <th class="excel-action-col"></th>
                                    </tr>
                                </thead>
                                <tbody id="report-rows"><tr><td colspan="9" class="empty">Select a date range to generate a report.</td></tr></tbody>
                            </table>
                        </div>
                        <div class="excel-statusbar">
                            <span id="excel-status-summary" class="excel-status-text"></span>
                        </div>
                    </div>
                    <div id="report-empty-state" class="report-empty-state">
                        <div class="report-empty-inner">
                            <svg class="report-empty-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            <p class="report-empty-title">No report generated yet</p>
                            <p class="muted">Select a start and end date above, then click <strong>Generate report</strong> to view attendance data in spreadsheet form.</p>
                        </div>
                    </div>
                </article>
            </section>
        </section>
    </div>

    <dialog id="scanner-dialog" class="modal">
        <div class="modal-heading"><div><p class="eyebrow">ATTENDANCE CHECK-IN</p><h2>Scan student QR</h2></div><button class="icon-button close-dialog" aria-label="Close">×</button></div>
        <p class="muted">Point the camera at a student's personal QR code.</p>
        <div id="reader" class="reader"></div>
        <p id="scan-result" class="scan-result" role="status">Camera permission may be required.</p>
        <button id="scanner-stop" class="button secondary wide">Stop camera</button>
    </dialog>

    <dialog id="qr-dialog" class="modal qr-modal">
        <div class="modal-heading"><div><p class="eyebrow">STUDENT QR CODE</p><h2 id="qr-student-name">Student</h2></div><button class="icon-button close-dialog" aria-label="Close">×</button></div>
        <p id="qr-student-meta" class="muted"></p>
        <canvas id="qr-canvas" width="280" height="280"></canvas>
        <div class="modal-actions"><button id="download-qr" class="button primary">Download QR</button><button id="print-qr" class="button secondary">Print QR</button></div>
    </dialog>

    <dialog id="attendance-dialog" class="modal">
        <div class="modal-heading"><div><p class="eyebrow">ADVISER ACTION</p><h2>Adjust attendance</h2></div><button class="icon-button close-dialog" aria-label="Close">×</button></div>
        <form id="attendance-form">
            <input id="attendance-student-id" type="hidden">
            <label>Student<select id="attendance-student" required></select></label>
            <label>Date<input id="attendance-date" type="date" required></label>
            <label>Status<select id="attendance-status"><option value="present">Present</option><option value="absent">Absent</option><option value="excused">Excused</option></select></label>
            <label>Note (optional)<textarea id="attendance-note" maxlength="500" rows="3"></textarea></label>
            <div class="modal-actions"><button class="button primary" type="submit">Save change</button><button class="button secondary close-dialog" type="button">Cancel</button></div>
        </form>
    </dialog>
    <dialog id="teacher-edit-dialog" class="modal">
        <div class="modal-heading"><div><p class="eyebrow">TEACHER ACCOUNT</p><h2>Edit teacher</h2></div><button class="icon-button close-dialog" aria-label="Close">×</button></div>
        <form id="teacher-edit-form">
            <input name="id" type="hidden">
            <label>Teacher name<input name="full_name" maxlength="160" autocomplete="name" required></label>
            <label>Username or email<input name="username" maxlength="120" autocomplete="username" required></label>
            <label>New password (leave blank to keep current)
                <div class="password-field-wrap">
                    <input name="password" type="password" minlength="12" maxlength="1024" autocomplete="new-password">
                    <button type="button" class="password-toggle-btn" aria-label="Show password" title="Show password" tabindex="-1">
                        <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </label>
            <label class="checkbox-label"><input name="is_active" type="checkbox"> Account is active</label>
            <div class="modal-actions"><button class="button primary" type="submit">Save teacher</button><button class="button secondary close-dialog" type="button">Cancel</button></div>
            <p id="teacher-edit-error" class="error-text" role="alert"></p>
        </form>
    </dialog>

    <dialog id="adviser-profile-dialog" class="modal profile-modal">
        <div class="modal-heading">
            <div>
                <p class="eyebrow">TEACHER PROFILE</p>
                <h2>Adviser Profile</h2>
            </div>
            <button class="icon-button close-dialog" aria-label="Close">×</button>
        </div>
        <form id="adviser-profile-form">
            <div class="profile-photo-container">
                <div class="profile-photo-preview" id="profile-photo-preview">
                    <span id="profile-photo-initials">A</span>
                    <img id="profile-photo-display" src="" alt="Teacher display photo" hidden>
                </div>
                <div class="profile-photo-controls">
                    <label class="button secondary small photo-btn">
                        <span>Upload photo</span>
                        <input type="file" id="profile-photo-input" accept="image/png,image/jpeg,image/webp,image/gif" hidden>
                    </label>
                    <button type="button" id="profile-photo-remove" class="button secondary small" hidden>Remove photo</button>
                    <p class="muted profile-photo-tip">Upload a display photo for your teacher profile.</p>
                </div>
            </div>

            <label>Teacher's Name
                <input name="teacher_name" id="profile-name-input" maxlength="160" autocomplete="name" placeholder="Enter teacher's full name" required>
            </label>

            <label>Currently Handling / Teaching Section
                <input name="teaching_section" id="profile-section-input" maxlength="120" placeholder="e.g. COMPASSION" required>
            </label>

            <div class="modal-actions">
                <button class="button primary" type="submit">Save Profile</button>
                <button class="button secondary close-dialog" type="button">Cancel</button>
            </div>
            <p id="profile-save-status" class="settings-status" role="status" aria-live="polite"></p>
        </form>
    </dialog>
</body>
</html>
