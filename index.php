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
                <div class="welcome-row">
                    <div>
                        <h2>Attendance reports</h2>
                        <p class="muted">Official DepEd School Form 2 (SF2-SHS) monthly report and custom date logs.</p>
                    </div>
                    <div class="report-view-toggle">
                        <button id="report-tab-sf2" class="button primary button-sm tab-btn active" type="button">School Form 2 (SF2-SHS)</button>
                        <button id="report-tab-log" class="button secondary button-sm tab-btn" type="button">Date-Range Log</button>
                    </div>
                </div>

                <!-- SF-2 DepEd Monthly Attendance Panel -->
                <article id="sf2-panel" class="panel sf2-panel-container">
                    <div class="sf2-control-bar">
                        <div class="sf2-controls-grid sf2-controls-simple">
                            <label>Month
                                <input id="sf2-month-input" type="month" required>
                            </label>
                            <label>Section
                                <select id="sf2-section-select">
                                    <option value="">All Sections</option>
                                </select>
                            </label>
                            <label>Grade Level
                                <input id="sf2-grade-input" value="Grade 12">
                            </label>
                            <label>School Year
                                <input id="sf2-sy-input" value="2024 - 2025">
                            </label>

                            <!-- Hidden DepEd school metadata defaults -->
                            <input type="hidden" id="sf2-track-input" value="Academic Track - Science Technology, Engineering, and Mathematics">
                            <input type="hidden" id="sf2-sem-input" value="Second Semester">
                            <input type="hidden" id="sf2-school-input" value="Mercedes NHS">
                            <input type="hidden" id="sf2-schoolid-input" value="303929">
                            <input type="hidden" id="sf2-district-input" value="Mercedes">
                            <input type="hidden" id="sf2-division-input" value="Zamboanga City">
                            <input type="hidden" id="sf2-region-input" value="Region IX">
                            <input type="hidden" id="sf2-course-input" value="">
                        </div>
                        <div class="sf2-actions-row">
                            <button class="button primary" id="sf2-generate-btn" type="button">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <span>Generate SF-2</span>
                            </button>
                            <button class="button secondary" id="sf2-view-btn" type="button">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <span>View SF-2</span>
                            </button>
                        </div>
                    </div>

                    <!-- Bree XLSX Editor Frame matching user's screenshot -->
                    <div class="xlsx-window-frame" id="sf2-xlsx-frame">
                        <!-- Window Titlebar -->
                        <div class="xlsx-titlebar">
                            <div class="xlsx-titlebar-left">
                                <svg class="xlsx-app-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
                                <div class="xlsx-window-title">Bree XLSX Editor - School-Form-2-SF-2.xls</div>
                            </div>
                            <div class="xlsx-window-controls" aria-hidden="true">
                                <button type="button" class="xlsx-win-btn win-min" title="Minimize">&#9472;</button>
                                <button type="button" class="xlsx-win-btn win-max" title="Maximize">&#9633;</button>
                                <button type="button" class="xlsx-win-btn win-close" title="Close">&#10005;</button>
                            </div>
                        </div>

                        <!-- Menu Tabs Bar (File, Home, Insert, etc.) -->
                        <div class="xlsx-menubar">
                            <div class="xlsx-menu-tabs">
                                <span class="xlsx-menu-tab active">File</span>
                                <span class="xlsx-menu-tab">Home</span>
                                <span class="xlsx-menu-tab">Insert</span>
                                <span class="xlsx-menu-tab">Page Layout</span>
                                <span class="xlsx-menu-tab">Formulas</span>
                                <span class="xlsx-menu-tab">Data</span>
                                <span class="xlsx-menu-tab">Review</span>
                                <span class="xlsx-menu-tab">View</span>
                            </div>
                        </div>

                        <!-- Ribbon Toolbar matching screenshot -->
                        <div class="xlsx-ribbon">
                            <!-- Group: Common -->
                            <div class="xlsx-ribbon-group">
                                <div class="xlsx-ribbon-items">
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-new" title="New file">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                                        <span>New</span>
                                    </button>
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-open" title="Open file">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                        <span>Open</span>
                                    </button>
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-save" title="Save file">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                        <span>Save</span>
                                    </button>
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-saveas" title="Save As">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><path d="M12 3v5"/></svg>
                                        <span>Save As</span>
                                    </button>
                                    <button type="button" class="xlsx-btn" id="xlsx-quick-print" title="Quick Print">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/><polygon points="12,16 14,19 10,19" fill="#16a34a"/></svg>
                                        <span>Quick Print</span>
                                    </button>
                                    <button type="button" class="xlsx-btn xlsx-action-highlight" id="xlsx-print-action" title="Print document (DepEd SF-2)">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                        <span>Print</span>
                                    </button>
                                    <button type="button" class="xlsx-btn" id="xlsx-preview-action" title="Print preview">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><circle cx="15" cy="15" r="4"/><line x1="18" y1="18" x2="21" y2="21"/></svg>
                                        <span>Print Preview</span>
                                    </button>
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-undo" title="Undo">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                        <span>Undo</span>
                                    </button>
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-redo" title="Redo">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                        <span>Redo</span>
                                    </button>
                                </div>
                                <div class="xlsx-group-label">Common</div>
                            </div>

                            <!-- Group: Info -->
                            <div class="xlsx-ribbon-group">
                                <div class="xlsx-ribbon-items">
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-encrypt" title="Encrypt with Password">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1.5"/></svg>
                                        <span>Encrypt with Password</span>
                                    </button>
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-properties" title="Document Properties">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><circle cx="12" cy="14" r="3"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                                        <span>Document Properties</span>
                                    </button>
                                </div>
                                <div class="xlsx-group-label">Info</div>
                            </div>

                            <!-- Group: Help -->
                            <div class="xlsx-ribbon-group">
                                <div class="xlsx-ribbon-items">
                                    <button type="button" class="xlsx-btn" id="xlsx-btn-upgrade" title="Upgrade">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>Upgrade</span>
                                    </button>
                                </div>
                                <div class="xlsx-group-label">Help</div>
                            </div>
                        </div>

                        <!-- Formula Bar matching screenshot -->
                        <div class="xlsx-formulabar">
                            <div class="xlsx-cell-address" id="xlsx-current-cell">BK109</div>
                            <div class="xlsx-formula-actions" aria-hidden="true">
                                <span class="fx-action fx-cancel" title="Cancel">&#10005;</span>
                                <span class="fx-action fx-check" title="Enter">&#10003;</span>
                                <span class="fx-action fx-fn" title="Insert Function">fx</span>
                            </div>
                            <input type="text" class="xlsx-formula-input" id="xlsx-formula-text" value="=School Form 2 Daily Attendance Report of Learners For Senior High School (SF2-SHS)" readonly aria-label="Formula bar">
                        </div>

                        <!-- Top Column Headers Strip (matching screenshot EFF, G, J, K, L, M, N...) -->
                        <div class="xlsx-col-headers-strip" aria-hidden="true">
                            <div class="xlsx-corner-cell"></div>
                            <div class="xlsx-col-letters">
                                <span>EFF</span><span>G</span><span>J</span><span>K</span><span>L</span><span>M</span><span>N</span><span>P</span><span>R</span><span>S</span><span>T</span><span>U</span><span>V</span><span>W</span><span>Y</span><span>Z</span><span>AA</span><span>AB</span><span>AG</span><span>AH</span><span>AI</span><span>AJ</span><span>AK</span><span>AL</span><span>AM</span><span>AN</span><span>AO</span><span>AP</span><span>AQ</span><span>AR</span><span>AS</span><span>AT</span><span>AU</span><span>AV</span><span>AW</span><span>AX</span><span>AY</span><span>AZ</span><span>BA</span><span>BB</span><span>BC</span><span>BD</span><span>BE</span><span>BF</span><span>BG</span><span>BK</span><span>BL</span><span>BM</span><span>BN</span><span>BO</span><span>BR</span><span>BS</span><span>BT</span><span>BV</span><span>BW</span><span>BX</span><span>BZ</span><span>CB</span><span>CC</span><span>CD</span><span>CE</span><span>CF</span><span>CG</span><span>CH</span><span>CI</span>
                            </div>
                        </div>

                        <!-- Viewport / Printable Area -->
                        <div class="xlsx-sheet-viewport" id="sf2-printable-area">
                            <div class="sf2-official-sheet">
                                <!-- Top Official DepEd Header -->
                                <div class="sf2-header-container">
                                    <div class="sf2-logo-left">
                                        <!-- Kagawaran ng Edukasyon Official Seal -->
                                        <svg class="sf2-logo-img" viewBox="0 0 100 100" width="70" height="70" aria-label="DepEd Seal">
                                            <circle cx="50" cy="50" r="48" fill="#ffffff" stroke="#0038a8" stroke-width="2"/>
                                            <circle cx="50" cy="50" r="42" fill="none" stroke="#0038a8" stroke-width="1.2" stroke-dasharray="2,2"/>
                                            <path id="seal-path-top" d="M 17 50 A 33 33 0 0 1 83 50" fill="none"/>
                                            <path id="seal-path-bot" d="M 83 50 A 33 33 0 0 1 17 50" fill="none"/>
                                            <text font-size="7" font-weight="bold" fill="#0038a8"><textPath href="#seal-path-top" startOffset="50%" text-anchor="middle">REPUBLIKA NG PILIPINAS</textPath></text>
                                            <text font-size="6.5" font-weight="bold" fill="#0038a8"><textPath href="#seal-path-bot" startOffset="50%" text-anchor="middle">KAGAWARAN NG EDUKASYON</textPath></text>
                                            <path d="M 50 26 L 50 74 A 24 24 0 0 0 50 26" fill="#0038a8"/>
                                            <path d="M 50 26 L 50 74 A 24 24 0 0 1 50 26" fill="#ce1126"/>
                                            <polygon points="50,38 52,43 57,43 53,46 55,51 50,48 45,51 47,46 43,43 48,43" fill="#fcd116"/>
                                            <circle cx="50" cy="50" r="4" fill="#fcd116"/>
                                        </svg>
                                    </div>
                                    <div class="sf2-header-center">
                                        <h1 class="sf2-form-title">School Form 2 Daily Attendance Report of Learners For Senior High School (SF2-SHS)</h1>
                                    </div>
                                    <div class="sf2-logo-right">
                                        <!-- Official DepEd Logo -->
                                        <svg class="sf2-logo-img" viewBox="0 0 160 70" width="125" height="55" aria-label="DepEd Logo">
                                            <path d="M 75 7 C 73 13 78 17 78 21 C 78 25 74 27 72 23 C 71 20 72 16 71 13 C 68 17 67 22 68 27 C 69 32 73 36 78 36 C 84 36 88 31 88 25 C 88 17 81 11 75 7 Z" fill="#e11d48"/>
                                            <path d="M 76 17 C 75 21 78 24 78 27 C 78 30 75 32 74 29 C 73 27 74 24 73 22 C 71 25 71 28 72 31 C 73 33 75 35 78 35 C 81 35 83 32 83 28 C 83 23 79 19 76 17 Z" fill="#f59e0b"/>
                                            <text x="80" y="46" font-family="'Times New Roman', serif" font-size="28" font-weight="bold" fill="#0038a8" text-anchor="middle">Dep<tspan font-size="32">ED</tspan></text>
                                            <text x="80" y="58" font-family="Arial, sans-serif" font-size="7" font-weight="700" fill="#0038a8" letter-spacing="1.2" text-anchor="middle">DEPARTMENT OF EDUCATION</text>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Metadata Box Table -->
                                <table class="sf2-meta-table">
                                    <tbody>
                                        <tr>
                                            <td class="meta-label">School Name</td>
                                            <td class="meta-val meta-school" id="disp-school">Mercedes NHS</td>
                                            <td class="meta-label">School ID</td>
                                            <td class="meta-val" id="disp-schoolid">303929</td>
                                            <td class="meta-label">District</td>
                                            <td class="meta-val" id="disp-district">Mercedes</td>
                                            <td class="meta-label">Division</td>
                                            <td class="meta-val" id="disp-division">Zamboanga City</td>
                                            <td class="meta-label">Region</td>
                                            <td class="meta-val" id="disp-region">Region IX</td>
                                        </tr>
                                        <tr>
                                            <td class="meta-label">Semester</td>
                                            <td class="meta-val" id="disp-sem">Second Semester</td>
                                            <td class="meta-label">School Year</td>
                                            <td class="meta-val" id="disp-sy">2024 - 2025</td>
                                            <td class="meta-label">Grade Level</td>
                                            <td class="meta-val" id="disp-grade">Grade 12</td>
                                            <td class="meta-label">Track and Strand</td>
                                            <td class="meta-val" colspan="3" id="disp-track">Academic Track - Science Technology, Engineering, and Mathematics</td>
                                        </tr>
                                        <tr>
                                            <td class="meta-label">Section</td>
                                            <td class="meta-val" id="disp-section">STEM</td>
                                            <td class="meta-label" colspan="2">Course (for TVL only)</td>
                                            <td class="meta-val" colspan="2" id="disp-course"></td>
                                            <td class="meta-label">Month of</td>
                                            <td class="meta-val meta-month-val" colspan="3" id="disp-month">October 2026</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Main Attendance Grid -->
                                <div class="sf2-grid-wrap">
                                    <table class="sf2-main-grid" id="sf2-main-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="col-num">No.</th>
                                                <th rowspan="2" class="col-name">NAME<br><span class="th-sub">(Last Name, First Name, Middle Name)</span></th>
                                                <th colspan="25" class="col-date-span">DATE</th>
                                                <th colspan="2" class="col-total-span">Total for the Month</th>
                                                <th rowspan="2" class="col-remarks">REMARKS<br><span class="th-sub">(If NLIS, state reason, please refer to legend number 2. If TRANSFERRED IN/OUT, write the name of School.)</span></th>
                                            </tr>
                                            <tr id="sf2-day-cols-header">
                                                <!-- Dynamic 25 weekday columns (M T W TH F × 5) with date numbers -->
                                            </tr>
                                        </thead>
                                        <tbody id="sf2-table-body">
                                            <!-- Dynamically filled with Male students, Total Male, Female students, Total Female, Combined -->
                                            <tr><td colspan="30" class="empty">Click <strong>Generate SF-2</strong> to populate this sheet with classroom attendance data.</td></tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- SF-2 DepEd Footer & Certification -->
                                <div class="sf2-footer-section">
                                    <div class="sf2-footer-summary">
                                        <table class="sf2-summary-table">
                                            <thead>
                                                <tr>
                                                    <th>Summary</th>
                                                    <th>MALE</th>
                                                    <th>FEMALE</th>
                                                    <th>TOTAL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Total Registered Learners</td>
                                                    <td id="sf2-sum-male-reg">0</td>
                                                    <td id="sf2-sum-fem-reg">0</td>
                                                    <td id="sf2-sum-tot-reg">0</td>
                                                </tr>
                                                <tr>
                                                    <td>Daily Average Attendance</td>
                                                    <td id="sf2-sum-male-avg">0</td>
                                                    <td id="sf2-sum-fem-avg">0</td>
                                                    <td id="sf2-sum-tot-avg">0</td>
                                                </tr>
                                                <tr>
                                                    <td>Percentage of Attendance</td>
                                                    <td id="sf2-sum-male-pct">0%</td>
                                                    <td id="sf2-sum-fem-pct">0%</td>
                                                    <td id="sf2-sum-tot-pct">0%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="sf2-footer-legend">
                                        <p class="legend-title"><strong>GUIDELINES:</strong></p>
                                        <p class="legend-line">1. Attendance shall be recorded daily. Write <strong>X</strong> in the column if learner is absent, leave <strong>blank</strong> if present.</p>
                                        <p class="legend-line">2. Reasons for Dropout/NLIS: <strong>A</strong>-Domestic, <strong>B</strong>-Individual, <strong>C</strong>-School-related, <strong>D</strong>-Geographic, <strong>E</strong>-Financial.</p>
                                    </div>
                                    <div class="sf2-footer-cert">
                                        <p class="cert-text">I certify that this is a true and correct report.</p>
                                        <div class="cert-signature-box">
                                            <div class="signature-line">
                                                <strong id="sf2-adviser-sig">Adviser Name</strong>
                                                <small>(Signature of Teacher / Adviser)</small>
                                            </div>
                                            <div class="signature-line">
                                                <strong id="sf2-head-sig">School Head / Principal</strong>
                                                <small>(Signature of School Head)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Spreadsheet Sheet Tab Bar matching screenshot -->
                        <div class="xlsx-sheet-tab-bar">
                            <div class="xlsx-tab-nav" aria-hidden="true">
                                <span>&#9664;</span><span>&#9654;</span>
                            </div>
                            <div class="xlsx-tab active">
                                <span class="tab-text">school form 2 shs ver2018.2.1.1</span>
                            </div>
                            <div class="xlsx-tab-add" aria-hidden="true">+</div>
                            <div class="xlsx-tab-spacer"></div>
                            <div class="xlsx-zoom-control">
                                <span>100%</span>
                                <input type="range" min="50" max="150" value="100" class="xlsx-zoom-slider" disabled>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Date-Range Attendance Log (Toggleable) -->
                <article id="standard-report-panel" class="panel report-panel" hidden>
                    <div class="panel-heading"><div><h3>Date-range attendance log</h3><p class="muted">Select a date range to review individual daily check-in logs.</p></div></div>
                    <form id="report-form" class="report-controls">
                        <label>Start date<input id="report-start" type="date" required></label>
                        <label>End date<input id="report-end" type="date" required></label>
                        <button class="button primary" type="submit">Generate log</button>
                    </form>
                    <div id="report-excel-view" class="report-excel-view" hidden>
                        <div class="excel-toolbar">
                            <div class="excel-toolbar-left">
                                <span class="excel-file-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </span>
                                <span id="excel-sheet-name" class="excel-sheet-tab">Attendance Log</span>
                            </div>
                            <div class="excel-toolbar-right">
                                <span id="report-row-count" class="excel-row-count"></span>
                                <button class="button secondary button-sm" id="print-report" type="button" disabled>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                    Print Log
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
                            <p class="report-empty-title">No log generated yet</p>
                            <p class="muted">Select a start and end date above, then click <strong>Generate log</strong> to view raw check-in records.</p>
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

    <!-- SF-2 Print Preview Modal -->
    <dialog id="sf2-preview-modal" class="sf2-preview-modal">
        <div class="sf2-preview-modal-header">
            <div>
                <p class="eyebrow">DepEd SF-2 SHS</p>
                <h2>School Form 2 (SF2-SHS) — Excel Form</h2>
            </div>
            <div class="sf2-preview-header-actions">
                <button id="sf2-preview-print-btn" class="button primary" type="button">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Print
                </button>
                <button class="icon-button close-dialog" type="button" aria-label="Close preview">×</button>
            </div>
        </div>
        <div id="sf2-preview-modal-content" class="sf2-preview-modal-body">
            <!-- Cloned printable SF-2 area injected here by JS -->
        </div>
    </dialog>

</body>
</html>
