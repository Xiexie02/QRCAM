<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#09090b">
    <title>QRCAMv2 — Attendance</title>
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="stylesheet" href="styles.css">
    <script defer src="assets/qrcode-browser.js"></script>
    <script defer src="assets/html5-qrcode.min.js"></script>
    <script defer src="app.js"></script>
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
                            <svg class="eye-icon eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg class="eye-icon eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
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
                <div class="profile"><div class="avatar" id="adviser-avatar">A</div><div class="profile-name"><strong id="adviser-name">Adviser</strong><small id="adviser-role">Administrator</small></div></div>
            </div>
        </aside>

        <section class="main-area">
            <header class="topbar">
                <button id="menu-toggle" class="icon-button menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="primary-navigation">☰</button>
                <div><p class="eyebrow">CLASSROOM CONSOLE</p><h1 id="page-title">Overview</h1></div>
                <div class="topbar-actions"><span class="topbar-date"><span id="header-date"></span></span><button id="theme-toggle" class="button secondary theme-toggle" type="button" aria-label="Switch to dark mode">Dark mode</button><button id="refresh-button" class="button secondary" type="button" aria-label="Refresh dashboard">Refresh</button></div>
            </header>
            <div id="notice" class="notice" role="status" hidden></div>

            <section id="dashboard-screen" class="screen">
                <div class="welcome-row"><div><h2>Today's overview</h2><p class="muted">Attendance and enrolled learner totals.</p></div><button id="open-scanner" class="button primary"><span>▣</span> Scan QR code</button></div>
                <div class="stats-grid">
                    <article class="stat-card"><div class="stat-top"><span>Overall students</span><span class="stat-icon neutral">♙</span></div><strong id="total-count">0</strong><small><span id="unknown-gender-count">0</span> gender unspecified</small></article>
                    <article class="stat-card"><div class="stat-top"><span>Male students</span><span class="stat-icon blue">♂</span></div><strong id="male-count">0</strong><small>Recorded as male</small></article>
                    <article class="stat-card"><div class="stat-top"><span>Female students</span><span class="stat-icon rose">♀</span></div><strong id="female-count">0</strong><small>Recorded as female</small></article>
                    <article class="stat-card"><div class="stat-top"><span>Present</span><span class="stat-icon green">✓</span></div><strong id="present-count">0</strong><small id="present-percent">0% of students</small></article>
                    <article class="stat-card"><div class="stat-top"><span>Absent</span><span class="stat-icon amber">—</span></div><strong id="absent-count">0</strong><small id="absent-percent">0% of students</small></article>
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
                                    <svg class="eye-icon eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <svg class="eye-icon eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
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
                                    <svg class="eye-icon eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <svg class="eye-icon eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                </button>
                            </div>
                        </label>
                        <label>New password (leave blank to keep current)
                            <div class="password-field-wrap">
                                <input name="new_password" type="password" minlength="12" maxlength="1024" autocomplete="new-password">
                                <button type="button" class="password-toggle-btn" aria-label="Show password" title="Show password" tabindex="-1">
                                    <svg class="eye-icon eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <svg class="eye-icon eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
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
                <div class="welcome-row"><div><h2>Attendance reports</h2><p class="muted">Build monthly SF-2 and date-range attendance reports.</p></div></div>
                <article id="sf2-report-panel" class="panel report-panel sf2-report-panel">
                    <div class="panel-heading sf2-heading"><div><h3>SF-2 Monthly Attendance</h3><p class="muted">Build a printable monthly roster from enrolled students.</p></div></div>
                    <form id="sf2-form" class="report-controls sf2-controls">
                        <label>School<input name="school" maxlength="160" value="Maria Clara L. Lobregat NHS"></label>
                        <label>School ID<input name="school_id" maxlength="30" value="303922"></label>
                        <label>Division<input name="division" maxlength="100" value="Zamboanga City"></label>
                        <label>District<input name="district" maxlength="100" placeholder="Not specified"></label>
                        <label>School year<input name="school_year" maxlength="20" value="2026-2027" required></label>
                        <label>Semester<input name="semester" maxlength="40" placeholder="If applicable"></label>
                        <label>Grade level<input name="grade" maxlength="30" value="Grade 10" required></label>
                        <label>Track / strand<input name="track" maxlength="100" placeholder="Optional"></label>
                        <label>Course (TVL only)<input name="course" maxlength="100" placeholder="Optional"></label>
                        <label>Section<input name="section" maxlength="120" value="COMPASSION"></label>
                        <label>Region<input name="region" maxlength="100" value="Region IX"></label>
                        <label>Month<input name="month" type="month" required></label>
                        <label>Adviser<input name="adviser" maxlength="160" placeholder="Adviser name"></label>
                        <button class="button secondary" id="sf2-import" type="button">Import SF1 roster CSV</button>
                        <button class="button primary" type="submit">Review SF-2</button>
                    </form>
                    <p class="sf2-guidance">This follows the attached Senior High School SF-2 layout. The attached SF1 is Grade 10, so verify the editable grade and school-year details before official use. Daily attendance and totals are intentionally blank; add an LRN reference column because the attached SF-2 template has no LRN column.</p>
                    <div id="sf2-sheet" class="sf2-sheet" hidden></div>
                </article>
                <article id="standard-report-panel" class="panel report-panel">
                    <form id="report-form" class="report-controls">
                        <label>Start date<input id="report-start" type="date" required></label>
                        <label>End date<input id="report-end" type="date" required></label>
                        <button class="button primary" type="submit">Generate report</button>
                        <button class="button secondary" id="print-report" type="button" disabled>Print report</button>
                    </form>
                    <div id="report-title" class="report-title"></div>
                    <div class="table-wrap"><table><thead><tr><th>DATE</th><th>STUDENT ID</th><th>STUDENT</th><th>SECTION</th><th>STATUS</th><th>TIME</th><th>NOTE</th><th></th></tr></thead><tbody id="report-rows"><tr><td colspan="8" class="empty">Select a date range to generate a report.</td></tr></tbody></table></div>
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
                        <svg class="eye-icon eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="eye-icon eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </label>
            <label class="checkbox-label"><input name="is_active" type="checkbox"> Account is active</label>
            <div class="modal-actions"><button class="button primary" type="submit">Save teacher</button><button class="button secondary close-dialog" type="button">Cancel</button></div>
            <p id="teacher-edit-error" class="error-text" role="alert"></p>
        </form>
    </dialog>
</body>
</html>
