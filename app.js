(() => {
    'use strict';

    const state = { csrf: '', students: [], dashboard: null, scanner: null, qrStudent: null, reportRows: [], role: 'admin', refreshTimer: null };
    const $ = (selector) => document.querySelector(selector);
    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    })[char]);
    const initials = (name) => String(name || '?').trim().split(/\s+/).slice(0, 2).map((part) => part[0] || '').join('').toUpperCase();

    async function api(action, options = {}) {
        const headers = new Headers(options.headers || {});
        if (options.body && !(options.body instanceof FormData)) headers.set('Content-Type', 'application/json');
        if (options.method && options.method !== 'GET' && state.csrf) headers.set('X-CSRF-Token', state.csrf);
        const response = await fetch(`api.php?action=${encodeURIComponent(action)}${options.query || ''}`, {
            ...options,
            headers,
            credentials: 'same-origin',
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(payload.error || `Request failed (${response.status}).`);
        return payload;
    }

    function notice(message, isError = false) {
        const node = $('#notice');
        if (!node) return;
        node.textContent = message;
        node.classList.toggle('error', isError);
        node.hidden = false;
        window.clearTimeout(notice.timer);
        notice.timer = window.setTimeout(() => { node.hidden = true; }, 6000);
    }

    const EYE_OPEN_SVG = '<svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    const EYE_CLOSED_SVG = '<svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';

    function getStoredTeacherProfile() {
        try {
            return JSON.parse(localStorage.getItem('qrcam_teacher_profile') || '{}');
        } catch {
            return {};
        }
    }

    function saveStoredTeacherProfile(profile) {
        try {
            localStorage.setItem('qrcam_teacher_profile', JSON.stringify(profile));
        } catch {
            // ignore
        }
    }

    function updateAdviserProfileUI(profile) {
        if (!profile) return;
        const name = profile.name || 'Adviser';
        const section = profile.section || '';
        const photo = profile.photo || '';

        const nameEl = $('#adviser-name');
        if (nameEl) nameEl.textContent = name;

        const roleTextEl = $('#adviser-section-text');
        if (roleTextEl) {
            roleTextEl.textContent = section ? `Section: ${section}` : (state.role === 'admin' ? 'Administrator' : 'Teacher');
        }

        const initialsEl = $('#adviser-avatar-initials');
        const imgEl = $('#adviser-avatar-img');
        if (photo && imgEl && initialsEl) {
            imgEl.src = photo;
            imgEl.hidden = false;
            initialsEl.hidden = true;
        } else if (initialsEl && imgEl) {
            initialsEl.textContent = initials(name);
            initialsEl.hidden = false;
            imgEl.hidden = true;
        }
    }

    function showAuthenticated(authenticated, adviser = '', role = 'admin', section = '') {
        $('#login-view').hidden = authenticated;
        $('#app-view').hidden = !authenticated;
        state.role = role;
        if (authenticated) {
            const stored = getStoredTeacherProfile();
            const profile = {
                name: stored.name || adviser || 'Adviser',
                section: stored.section || section || '',
                photo: stored.photo || '',
            };
            updateAdviserProfileUI(profile);
            const teacherMgmt = $('#teacher-management');
            if (teacherMgmt) teacherMgmt.hidden = role !== 'admin';
            const myAccount = $('#my-account-panel');
            if (myAccount) myAccount.hidden = role !== 'teacher';
            refreshDashboard();
            if (role === 'teacher') loadMyAccount();
        }
    }

    function applyTheme(theme) {
        const isDark = theme === 'dark';
        document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        localStorage.setItem('qrcam_theme', isDark ? 'dark' : 'light');
        const toggleBtn = $('#theme-toggle');
        if (toggleBtn) {
            toggleBtn.textContent = isDark ? 'Light mode' : 'Dark mode';
            toggleBtn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        }
        const themeSelect = $('select[name="theme"]');
        if (themeSelect) themeSelect.value = isDark ? 'dark' : 'light';
    }

    function initTheme() {
        const savedTheme = localStorage.getItem('qrcam_theme') || 'light';
        applyTheme(savedTheme);
        $('#theme-toggle')?.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
        $('select[name="theme"]')?.addEventListener('change', (event) => {
            applyTheme(event.target.value);
        });
    }

    function initSidebar() {
        const collapsed = localStorage.getItem('qrcam_sidebar_collapsed') === 'true';
        if (collapsed) $('.sidebar')?.classList.add('collapsed');
        $('#brand-toggle')?.addEventListener('click', () => {
            const sidebar = $('.sidebar');
            if (!sidebar) return;
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('qrcam_sidebar_collapsed', sidebar.classList.contains('collapsed'));
        });
        $('#refresh-button')?.addEventListener('click', refreshDashboard);
    }

    function initPasswordToggles() {
        document.addEventListener('click', (event) => {
            const button = event.target.closest('.password-toggle-btn');
            if (!button) return;
            event.preventDefault();
            const wrap = button.closest('.password-field-wrap');
            if (!wrap) return;
            const input = wrap.querySelector('input');
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            button.setAttribute('title', isPassword ? 'Hide password' : 'Show password');
            button.innerHTML = isPassword ? EYE_CLOSED_SVG : EYE_OPEN_SVG;
        });
    }

    function ensurePasswordToggles() {
        document.querySelectorAll('input[type="password"]').forEach((input) => {
            if (input.closest('.password-field-wrap')) return;
            const wrap = document.createElement('div');
            wrap.className = 'password-field-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'password-toggle-btn';
            button.setAttribute('aria-label', 'Show password');
            button.setAttribute('title', 'Show password');
            button.setAttribute('tabindex', '-1');
            button.innerHTML = EYE_OPEN_SVG;
            wrap.appendChild(button);
        });
    }

    function initAdviserProfile() {
        const profileBtn = $('#adviser-profile-btn');
        const dialog = $('#adviser-profile-dialog');
        const form = $('#adviser-profile-form');
        const nameInput = $('#profile-name-input');
        const sectionInput = $('#profile-section-input');
        const photoInput = $('#profile-photo-input');
        const removePhotoBtn = $('#profile-photo-remove');
        const previewDisplay = $('#profile-photo-display');
        const previewInitials = $('#profile-photo-initials');
        const statusEl = $('#profile-save-status');

        if (!profileBtn || !dialog || !form) return;

        let pendingPhoto = null;

        function updatePreview(photo, name) {
            if (photo) {
                previewDisplay.src = photo;
                previewDisplay.hidden = false;
                previewInitials.hidden = true;
                if (removePhotoBtn) removePhotoBtn.hidden = false;
            } else {
                previewDisplay.src = '';
                previewDisplay.hidden = true;
                previewInitials.textContent = initials(name || 'A');
                previewInitials.hidden = false;
                if (removePhotoBtn) removePhotoBtn.hidden = true;
            }
        }

        profileBtn.addEventListener('click', () => {
            const stored = getStoredTeacherProfile();
            const currentName = stored.name || $('#adviser-name')?.textContent || '';
            const currentSection = stored.section || (state.students[0]?.class_section) || '';
            const currentPhoto = stored.photo || '';

            if (nameInput) nameInput.value = currentName;
            if (sectionInput) sectionInput.value = currentSection;
            pendingPhoto = currentPhoto;
            updatePreview(currentPhoto, currentName);
            if (statusEl) {
                statusEl.textContent = '';
                statusEl.className = 'settings-status';
            }
            dialog.showModal();
        });

        photoInput?.addEventListener('change', () => {
            const file = photoInput.files?.[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                if (statusEl) {
                    statusEl.textContent = 'Image must be under 5 MB.';
                    statusEl.className = 'settings-status error-text';
                }
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const maxDim = 320;
                    let width = img.width;
                    let height = img.height;
                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    pendingPhoto = canvas.toDataURL('image/jpeg', 0.85);
                    updatePreview(pendingPhoto, nameInput?.value || 'A');
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        removePhotoBtn?.addEventListener('click', () => {
            pendingPhoto = '';
            if (photoInput) photoInput.value = '';
            updatePreview('', nameInput?.value || 'A');
        });

        nameInput?.addEventListener('input', () => {
            if (!pendingPhoto) {
                previewInitials.textContent = initials(nameInput.value || 'A');
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const teacherName = nameInput.value.trim();
            const teachingSection = sectionInput.value.trim();
            if (!teacherName) return;

            const profile = {
                name: teacherName,
                section: teachingSection,
                photo: pendingPhoto || '',
            };
            saveStoredTeacherProfile(profile);
            updateAdviserProfileUI(profile);

            const settingsAdviser = $('input[name="adviser"]');
            const settingsSection = $('input[name="section"]');
            if (settingsAdviser) settingsAdviser.value = teacherName;
            if (settingsSection && teachingSection) settingsSection.value = teachingSection;

            try {
                await api('set_profile', {
                    method: 'POST',
                    body: JSON.stringify({
                        teacher_name: teacherName,
                        teaching_section: teachingSection,
                    }),
                });
            } catch {
                // local save still succeeded
            }

            notice('Adviser profile updated successfully.');
            dialog.close();
        });
    }

    function isSummaryStudent(student) {
        if (!student) return false;
        const name = String(student.full_name || '').toUpperCase().trim();
        return name.includes('TOTAL MALE') ||
               name.includes('TOTAL FEMALE') ||
               name.includes('COMBINED') ||
               name.startsWith('<===') ||
               name.startsWith('<---');
    }

    // =========================================================================
    // SF-2 Report Engine
    // =========================================================================

    function initSF2Reports() {
        // Tab switching
        $('#report-tab-sf2')?.addEventListener('click', () => {
            $('#sf2-panel').hidden = false;
            $('#standard-report-panel').hidden = true;
            $('#report-tab-sf2').classList.add('active');
            $('#report-tab-sf2').className = 'button primary button-sm tab-btn';
            $('#report-tab-log').className = 'button secondary button-sm tab-btn';
        });
        $('#report-tab-log')?.addEventListener('click', () => {
            $('#sf2-panel').hidden = true;
            $('#standard-report-panel').hidden = false;
            $('#report-tab-sf2').className = 'button secondary button-sm tab-btn';
            $('#report-tab-log').className = 'button primary button-sm tab-btn';
        });

        // Set default month to current month
        const monthInput = $('#sf2-month-input');
        if (monthInput && !monthInput.value) {
            const now = new Date();
            monthInput.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
        }

        // Populate section options
        function populateSections() {
            const sel = $('#sf2-section-select');
            if (!sel) return;
            const sections = [...new Set(state.students.map(s => s.class_section).filter(Boolean))].sort();
            const current = sel.value;
            sel.innerHTML = '<option value="">All Sections</option>' +
                sections.map(s => `<option value="${esc(s)}">${esc(s)}</option>`).join('');
            if (sections.includes(current)) sel.value = current;
        }

        // Generate button
        $('#sf2-generate-btn')?.addEventListener('click', async () => {
            populateSections();
            const monthVal = $('#sf2-month-input')?.value;
            if (!monthVal) { notice('Please select a month.', true); return; }
            const sectionFilter = $('#sf2-section-select')?.value || '';
            await generateSF2(monthVal, sectionFilter);
        });

        // Print buttons (both toolbar and ribbon)
        function triggerSF2Print() {
            const body = $('#sf2-table-body');
            if (!body || body.querySelector('.empty')) {
                notice('Generate SF-2 first before printing.', true);
                return;
            }
            window.print();
        }
        $('#sf2-print-btn')?.addEventListener('click', triggerSF2Print);
        $('#xlsx-print-action')?.addEventListener('click', triggerSF2Print);
        $('#xlsx-quick-print')?.addEventListener('click', triggerSF2Print);

        // View button → open preview modal
        $('#sf2-view-btn')?.addEventListener('click', () => {
            const body = $('#sf2-table-body');
            if (!body || body.querySelector('.empty')) {
                notice('Generate SF-2 first before previewing.', true);
                return;
            }
            const modal = $('#sf2-preview-modal');
            if (modal) {
                // Clone the printable area into modal
                const printable = $('#sf2-printable-area');
                const container = $('#sf2-preview-modal-content');
                if (printable && container) {
                    container.innerHTML = '';
                    container.appendChild(printable.cloneNode(true));
                }
                modal.showModal();
            }
        });
        $('#sf2-preview-print-btn')?.addEventListener('click', triggerSF2Print);

        // Print Preview (ribbon) → also open modal
        $('#xlsx-preview-action')?.addEventListener('click', () => {
            const body = $('#sf2-table-body');
            if (!body || body.querySelector('.empty')) {
                notice('Generate SF-2 first to preview.', true);
                return;
            }
            const modal = $('#sf2-preview-modal');
            if (modal) {
                const printable = $('#sf2-printable-area');
                const container = $('#sf2-preview-modal-content');
                if (printable && container) {
                    container.innerHTML = '';
                    container.appendChild(printable.cloneNode(true));
                }
                modal.showModal();
            }
        });
    }

    async function generateSF2(monthStr, sectionFilter) {
        const btn = $('#sf2-generate-btn');
        if (btn) { btn.disabled = true; btn.querySelector('span').textContent = 'Generating…'; }

        try {
            // Parse the month
            const [year, month] = monthStr.split('-').map(Number);
            const monthName = new Date(year, month - 1, 1).toLocaleString('en-PH', { month: 'long' });

            // Build the 25 weekday date slots (5 weeks × M T W TH F)
            // We go through the month week by week, Mon–Fri
            const weekdays = []; // { day: number|null, label: string, date: string|null }
            // Find first Monday on or before the 1st of month
            const firstDay = new Date(year, month - 1, 1);
            // Week starts on Monday (1=Mon...5=Fri)
            const startOffset = (firstDay.getDay() + 6) % 7; // 0=Mon,4=Fri,5=Sat,6=Sun
            const weekStart = new Date(firstDay);
            weekStart.setDate(firstDay.getDate() - startOffset);

            const labels = ['M', 'T', 'W', 'TH', 'F'];
            for (let week = 0; week < 5; week++) {
                for (let dow = 0; dow < 5; dow++) {
                    const d = new Date(weekStart);
                    d.setDate(weekStart.getDate() + week * 7 + dow);
                    if (d.getMonth() + 1 === month && d.getFullYear() === year) {
                        weekdays.push({
                            dayNum: d.getDate(),
                            label: labels[dow],
                            dateStr: `${year}-${String(month).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`,
                        });
                    } else {
                        weekdays.push({ dayNum: null, label: labels[dow], dateStr: null });
                    }
                }
            }

            // Update metadata display
            const schoolName = $('#sf2-school-input')?.value || 'Mercedes NHS';
            const schoolId   = $('#sf2-schoolid-input')?.value || '303929';
            const district   = $('#sf2-district-input')?.value || 'Mercedes';
            const division   = $('#sf2-division-input')?.value || 'Zamboanga City';
            const region     = $('#sf2-region-input')?.value || 'Region IX';
            const sy         = $('#sf2-sy-input')?.value || '2024 - 2025';
            const grade      = $('#sf2-grade-input')?.value || 'Grade 12';
            const track      = $('#sf2-track-input')?.value || 'Academic Track - Science Technology, Engineering, and Mathematics';
            const sem        = $('#sf2-sem-input')?.value || 'Second Semester';
            const course     = $('#sf2-course-input')?.value || '';
            const section    = sectionFilter || $('#sf2-section-select option:first-child')?.textContent || 'STEM';

            if ($('#disp-school')) $('#disp-school').textContent = schoolName;
            if ($('#disp-schoolid')) $('#disp-schoolid').textContent = schoolId;
            if ($('#disp-district')) $('#disp-district').textContent = district;
            if ($('#disp-division')) $('#disp-division').textContent = division;
            if ($('#disp-region')) $('#disp-region').textContent = region;
            if ($('#disp-sem')) $('#disp-sem').textContent = sem;
            if ($('#disp-sy')) $('#disp-sy').textContent = sy;
            if ($('#disp-grade')) $('#disp-grade').textContent = grade;
            if ($('#disp-track')) $('#disp-track').textContent = track;
            if ($('#disp-section')) $('#disp-section').textContent = sectionFilter || 'All Sections';
            if ($('#disp-course')) $('#disp-course').textContent = course;
            if ($('#disp-month')) $('#disp-month').textContent = `${monthName} ${year}`;

            // Update formula bar
            if ($('#xlsx-formula-text')) {
                $('#xlsx-formula-text').value = `=${monthName} ${year} SF2-SHS – ${sectionFilter || 'All Sections'}`;
            }
            if ($('#sf2-active-label')) {
                $('#sf2-active-label').textContent = `${monthName} ${year} – ${sectionFilter || 'All Sections'}`;
            }

            // Render column headers
            const headerRow = $('#sf2-day-cols-header');
            if (headerRow) {
                headerRow.innerHTML = weekdays.map(w =>
                    `<th class="col-day${w.dateStr ? '' : ' col-day-empty'}">${w.dayNum ?? ''}<br><span class="day-label">${w.label}</span></th>`
                ).join('') +
                '<th class="col-total-abs">ABSENT</th><th class="col-total-pre">PRESENT</th>';
            }

            // Fetch attendance for the month
            const startDate = `${year}-${String(month).padStart(2,'0')}-01`;
            const lastDay = new Date(year, month, 0).getDate();
            const endDate = `${year}-${String(month).padStart(2,'0')}-${String(lastDay).padStart(2,'0')}`;

            const result = await api('report', { query: `&start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}` });
            const rows = result.rows || [];

            // Build lookup: "YYYY-MM-DD:student_no" → status
            const attendanceLookup = {};
            rows.forEach(r => {
                attendanceLookup[`${r.date}:${r.student_no}`] = r.status;
            });

            // Filter students by section
            let students = state.students.filter(s => !isSummaryStudent(s));
            if (sectionFilter) {
                students = students.filter(s => s.class_section === sectionFilter);
            }

            // Split by gender
            const maleStudents = students.filter(s => (s.gender || 'unknown').toLowerCase() === 'male');
            const femaleStudents = students.filter(s => (s.gender || 'unknown').toLowerCase() === 'female');

            // Build a student row
            function buildStudentRow(student, rowNum) {
                let absCount = 0;
                let preCount = 0;
                const cells = weekdays.map(w => {
                    if (!w.dateStr) return '<td class="col-day col-day-empty"></td>';
                    const status = attendanceLookup[`${w.dateStr}:${student.student_no}`] || 'absent';
                    // DepEd SF-2: X = PRESENT, blank = ABSENT (confirmed DepEd standard)
                    if (status === 'present') {
                        preCount++;
                        return '<td class="col-day att-present">X</td>';
                    } else {
                        absCount++;
                        return '<td class="col-day att-absent"></td>';
                    }
                }).join('');
                return `<tr>
                    <td class="col-num">${rowNum}</td>
                    <td class="col-name sf2-name">${esc(student.full_name)}</td>
                    ${cells}
                    <td class="col-total sf2-total">${absCount}</td>
                    <td class="col-total sf2-total">${preCount}</td>
                    <td class="col-remarks"></td>
                </tr>`;
            }

            // Build daily totals row
            function buildTotalsRow(label, group) {
                const dailyCounts = weekdays.map(w => {
                    if (!w.dateStr) return { pre: 0, abs: 0 };
                    let pre = 0, abs = 0;
                    group.forEach(s => {
                        const status = attendanceLookup[`${w.dateStr}:${s.student_no}`] || 'absent';
                        if (status === 'present') pre++; else abs++;
                    });
                    return { pre, abs };
                });
                const totalAbs = dailyCounts.reduce((sum, d) => sum + d.abs, 0);
                const totalPre = dailyCounts.reduce((sum, d) => sum + d.pre, 0);
                const cells = dailyCounts.map((d, i) =>
                    weekdays[i].dateStr
                        ? `<td class="col-day total-day">${d.pre}</td>`
                        : '<td class="col-day col-day-empty"></td>'
                ).join('');
                return `<tr class="sf2-subtotal-row">
                    <td class="col-num"></td>
                    <td class="col-name sf2-subtotal-label">${label}</td>
                    ${cells}
                    <td class="col-total sf2-total">${totalAbs}</td>
                    <td class="col-total sf2-total">${totalPre}</td>
                    <td class="col-remarks"></td>
                </tr>`;
            }

            // Assemble tbody
            let html = '';
            // Male section
            if (maleStudents.length > 0 || femaleStudents.length === 0) {
                maleStudents.forEach((s, i) => { html += buildStudentRow(s, i + 1); });
                html += buildTotalsRow('<=== TOTAL MALE', maleStudents);
            }
            // Female section
            if (femaleStudents.length > 0) {
                femaleStudents.forEach((s, i) => { html += buildStudentRow(s, i + 1); });
                html += buildTotalsRow('<=== TOTAL FEMALE', femaleStudents);
            }
            // Combined
            html += buildTotalsRow('<=== COMBINED', students);

            if (!html) {
                html = `<tr><td colspan="30" class="empty">No students found${sectionFilter ? ` in section "${sectionFilter}"` : ''}. Import students first.</td></tr>`;
            }

            $('#sf2-table-body').innerHTML = html;

            // Update summary table
            const maleCount = maleStudents.length;
            const femaleCount = femaleStudents.length;
            const totalCount = students.length;
            const schoolDays = weekdays.filter(w => w.dateStr).length;

            function avgPresent(group) {
                if (!group.length || !schoolDays) return 0;
                let total = 0;
                weekdays.forEach(w => {
                    if (!w.dateStr) return;
                    group.forEach(s => {
                        if ((attendanceLookup[`${w.dateStr}:${s.student_no}`] || 'absent') === 'present') total++;
                    });
                });
                return (total / schoolDays).toFixed(1);
            }

            function pctPresent(group) {
                if (!group.length || !schoolDays) return '0%';
                let pre = 0, total = 0;
                weekdays.forEach(w => {
                    if (!w.dateStr) return;
                    group.forEach(s => {
                        total++;
                        if ((attendanceLookup[`${w.dateStr}:${s.student_no}`] || 'absent') === 'present') pre++;
                    });
                });
                return total ? Math.round((pre / total) * 100) + '%' : '0%';
            }

            if ($('#sf2-sum-male-reg')) $('#sf2-sum-male-reg').textContent = maleCount;
            if ($('#sf2-sum-fem-reg')) $('#sf2-sum-fem-reg').textContent = femaleCount;
            if ($('#sf2-sum-tot-reg')) $('#sf2-sum-tot-reg').textContent = totalCount;
            if ($('#sf2-sum-male-avg')) $('#sf2-sum-male-avg').textContent = avgPresent(maleStudents);
            if ($('#sf2-sum-fem-avg')) $('#sf2-sum-fem-avg').textContent = avgPresent(femaleStudents);
            if ($('#sf2-sum-tot-avg')) $('#sf2-sum-tot-avg').textContent = avgPresent(students);
            if ($('#sf2-sum-male-pct')) $('#sf2-sum-male-pct').textContent = pctPresent(maleStudents);
            if ($('#sf2-sum-fem-pct')) $('#sf2-sum-fem-pct').textContent = pctPresent(femaleStudents);
            if ($('#sf2-sum-tot-pct')) $('#sf2-sum-tot-pct').textContent = pctPresent(students);

            // Adviser signature
            const profile = getStoredTeacherProfile();
            if ($('#sf2-adviser-sig') && profile.name) {
                $('#sf2-adviser-sig').textContent = profile.name;
            }

            notice(`SF-2 generated for ${monthName} ${year}.`);

        } catch (error) {
            notice(error.message, true);
        } finally {
            if (btn) { btn.disabled = false; btn.querySelector('span').textContent = 'Generate SF-2'; }
        }
    }

    async function init() {
        initTheme();
        initSidebar();
        initPasswordToggles();
        ensurePasswordToggles();
        initSettings();
        initAdviserProfile();
        initSF2Reports();
        try {
            const session = await api('bootstrap');
            state.csrf = session.csrf;
            showAuthenticated(session.authenticated, session.adviser, session.role || 'admin', session.section || '');
        } catch (error) {
            $('#login-view').hidden = false;
            $('#login-error').textContent = error.message;
        }
    }

    async function refreshDashboard() {
        if ($('#app-view').hidden) return;
        try {
            const data = await api('dashboard');
            state.dashboard = data;
            const realStudents = (data.students || []).filter((s) => !isSummaryStudent(s));
            state.students = realStudents;
            renderDashboard(data);
            if (!$('#students-screen').hidden) renderStudents(realStudents);
            $('#header-date').textContent = new Date(`${data.date}T00:00:00`).toLocaleDateString(undefined, {
                weekday: 'short', month: 'short', day: 'numeric', year: 'numeric',
            });
        } catch (error) {
            notice(error.message, true);
        }
    }

    function renderDashboard(data) {
        const rawStudents = data.students || [];
        let maleCount = data.maleCount;
        let femaleCount = data.femaleCount;
        let combinedCount = data.combinedCount ?? data.total;

        for (const s of rawStudents) {
            const name = String(s.full_name || '').toUpperCase().trim();
            if (name.includes('TOTAL MALE')) {
                const n = parseInt(String(s.student_no).replace(/\D/g, ''), 10);
                if (!isNaN(n) && n > 0) maleCount = n;
            } else if (name.includes('TOTAL FEMALE')) {
                const n = parseInt(String(s.student_no).replace(/\D/g, ''), 10);
                if (!isNaN(n) && n > 0) femaleCount = n;
            } else if (name.includes('COMBINED')) {
                const n = parseInt(String(s.student_no).replace(/\D/g, ''), 10);
                if (!isNaN(n) && n > 0) combinedCount = n;
            }
        }

        const realStudents = rawStudents.filter((s) => !isSummaryStudent(s));
        if (combinedCount === undefined || combinedCount === 0) {
            combinedCount = realStudents.length;
        }
        if (maleCount === undefined) {
            maleCount = realStudents.filter((s) => s.gender === 'male').length;
        }
        if (femaleCount === undefined) {
            femaleCount = realStudents.filter((s) => s.gender === 'female').length;
        }

        const present = data.present || 0;
        const absent = data.absent !== undefined ? data.absent : Math.max(0, combinedCount - present);

        $('#total-count').textContent = combinedCount;
        $('#present-count').textContent = present;
        $('#absent-count').textContent = absent;
        if ($('#male-count')) $('#male-count').textContent = maleCount;
        if ($('#female-count')) $('#female-count').textContent = femaleCount;

        const presentRate = combinedCount ? Math.round(present / combinedCount * 100) : 0;
        const absentRate = combinedCount ? Math.round(absent / combinedCount * 100) : 0;
        $('#present-percent').textContent = `${presentRate}% of students`;
        $('#absent-percent').textContent = `${absentRate}% of students`;

        const students = Object.fromEntries(realStudents.map((student) => [student.id, student]));
        $('#audit-rows').innerHTML = data.auditLogs && data.auditLogs.length ? data.auditLogs.map((log) => {
            const before = log.before_data || null;
            const after = log.after_data || {};
            const student = students[after.student_id];
            const statusChange = before
                ? `${esc(before.status || 'unrecorded')} → ${esc(after.status || 'unknown')}`
                : `set to ${esc(after.status || 'unknown')}`;
            const noteChange = before && before.note !== after.note
                ? ` · note: ${esc(before.note || '—')} → ${esc(after.note || '—')}`
                : (after.note ? ` · ${esc(after.note)}` : '');
            const change = `${esc(student?.full_name || after.student_id || '')} · ${esc(after.attendance_date || '')}: ${statusChange}${noteChange}`;
            const stamp = log.created_at ? new Date(String(log.created_at).replace(' ', 'T')).toLocaleString() : '';
            return `<tr><td>${esc(stamp)}</td><td>${esc(log.actor)}</td><td>${change}</td></tr>`;
        }).join('') : '<tr><td colspan="3" class="empty">No manual changes recorded.</td></tr>';

        const feed = (data.attendance || []).slice().sort((a, b) => String(b.attended_at).localeCompare(String(a.attended_at)));
        $('#feed-rows').innerHTML = feed.length ? feed.map((entry) => {
            const student = students[entry.student_id];
            if (!student) return '';
            const time = entry.attended_at ? new Date(entry.attended_at.replace(' ', 'T')).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : '—';
            return `<tr>
                <td><div class="student-cell"><span class="student-avatar">${esc(initials(student.full_name))}</span><span><strong>${esc(student.full_name)}</strong><small>${esc(student.student_no)}</small></span></div></td>
                <td>${esc(time)}</td><td><span class="status-badge ${esc(entry.status)}">${esc(entry.status)}</span></td>
                <td><button class="text-button adjust-attendance" data-student="${esc(student.id)}" data-date="${esc(data.date)}">Adjust</button></td>
            </tr>`;
        }).join('') : '<tr><td colspan="4" class="empty">No attendance recorded today.</td></tr>';
    }

    function renderStudents(students) {
        const realStudents = (students || []).filter((s) => !isSummaryStudent(s));
        state.students = realStudents;
        $('#roster-count').textContent = `${realStudents.length} ${realStudents.length === 1 ? 'student' : 'students'}`;
        const query = $('#student-search').value.trim().toLowerCase();
        const visible = realStudents.filter((student) => `${student.full_name} ${student.student_no} ${student.class_section}`.toLowerCase().includes(query));
        $('#student-rows').innerHTML = visible.length ? visible.map((student) => `<tr>
            <td><div class="student-cell"><span class="student-avatar">${esc(initials(student.full_name))}</span><strong>${esc(student.full_name)}</strong></div></td>
            <td>${esc(student.student_no)}</td><td>${esc(student.class_section || '—')}</td>
            <td>${esc(String(student.created_at || '').slice(0, 10) || '—')}</td>
            <td><button class="text-button show-qr" data-student="${esc(student.id)}">View QR</button><button class="text-button adjust-attendance" data-student="${esc(student.id)}">Adjust</button></td>
        </tr>`).join('') : `<tr><td colspan="5" class="empty">${realStudents.length ? 'No matching students.' : 'No students enrolled yet. Import a CSV roster to get started.'}</td></tr>`;
    }

    function setScreen(name) {
        const labels = { dashboard: 'Overview', students: 'Students', reports: 'Reports', settings: 'Settings' };
        document.querySelectorAll('.screen').forEach((screen) => { screen.hidden = screen.id !== `${name}-screen`; });
        document.querySelectorAll('.nav-link').forEach((button) => button.classList.toggle('active', button.dataset.screen === name));
        $('#page-title').textContent = labels[name] || 'Overview';
        $('.sidebar').classList.remove('open');
        if (name === 'students') refreshDashboard();
        if (name === 'settings') loadTeacherAccounts();
    }

    async function loadStudents() {
        const result = await api('students');
        renderStudents(result.students);
    }

    async function loadTeacherAccounts() {
        if (state.role !== 'admin') return;
        const tbody = $('#teacher-account-rows');
        if (!tbody) return;
        try {
            const data = await api('teacher_accounts');
            const accounts = data.accounts || [];
            tbody.innerHTML = accounts.length ? accounts.map((acc) => `<tr>
                <td><strong>${esc(acc.full_name)}</strong></td>
                <td>${esc(acc.username)}</td>
                <td><span class="status-badge ${acc.is_active ? 'present' : 'absent'}">${acc.is_active ? 'Active' : 'Inactive'}</span></td>
                <td><button class="text-button edit-teacher" data-id="${esc(acc.id)}" data-name="${esc(acc.full_name)}" data-username="${esc(acc.username)}" data-active="${acc.is_active ? '1' : '0'}">Edit</button></td>
            </tr>`).join('') : '<tr><td colspan="4" class="empty">No teacher accounts created yet.</td></tr>';
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="4" class="empty error-text">${esc(error.message)}</td></tr>`;
        }
    }

    async function loadMyAccount() {
        if (state.role !== 'teacher') return;
        const form = $('#my-account-form');
        if (!form) return;
        try {
            const data = await api('my_account');
            if (data.account) {
                if (form.elements.full_name) form.elements.full_name.value = data.account.full_name || '';
                if (form.elements.username) form.elements.username.value = data.account.username || '';
            }
        } catch (_) {}
    }

    async function startScanner() {
        const dialog = $('#scanner-dialog');
        const result = $('#scan-result');
        dialog.showModal();
        result.textContent = 'Starting camera…';
        if (!window.Html5Qrcode) {
            result.textContent = 'QR scanner library is missing. Run npm install in the project folder.';
            return;
        }
        try {
            state.scanner = new Html5Qrcode('reader');
            await state.scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                async (decodedText) => {
                    if (!state.scanner) return;
                    const scanner = state.scanner;
                    state.scanner = null;
                    await scanner.stop().catch(() => {});
                    try {
                        const payload = await api('scan', { method: 'POST', body: JSON.stringify({ token: decodedText.trim() }) });
                        const time = payload.attendance.attended_at ? new Date(payload.attendance.attended_at.replace(' ', 'T')).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : '';
                        result.textContent = payload.duplicate
                            ? `${payload.student.full_name} is already marked present today (${time}).`
                            : `${payload.student.full_name} marked present at ${time}.`;
                        refreshDashboard();
                    } catch (error) {
                        result.textContent = error.message;
                    }
                },
                () => {},
            );
            result.textContent = 'Camera is ready. Hold the QR code in the frame.';
        } catch (error) {
            state.scanner = null;
            result.textContent = `Could not start camera: ${error.message}`;
        }
    }

    async function stopScanner() {
        if (state.scanner) {
            const scanner = state.scanner;
            state.scanner = null;
            await scanner.stop().catch(() => {});
        }
        const reader = $('#reader');
        reader.replaceChildren();
    }

    async function openQr(studentId) {
        const student = state.students.find((item) => item.id === studentId);
        if (!student) return;
        if (!window.QRCode) {
            notice('QR library is missing. Run npm install in the project folder.', true);
            return;
        }
        state.qrStudent = student;
        $('#qr-student-name').textContent = student.full_name;
        $('#qr-student-meta').textContent = `${student.student_no} · ${student.class_section || 'No section'}`;
        await QRCode.toCanvas($('#qr-canvas'), student.qr_token, {
            width: 280, margin: 2, errorCorrectionLevel: 'H', color: { dark: '#000000', light: '#ffffff' },
        });
        $('#qr-dialog').showModal();
    }

    function openAttendance(studentId = '', date = '') {
        $('#attendance-student').innerHTML = state.students.map((student) =>
            `<option value="${esc(student.id)}">${esc(student.full_name)} · ${esc(student.student_no)}</option>`).join('');
        $('#attendance-student').value = studentId || state.students[0]?.id || '';
        $('#attendance-date').value = date || state.dashboard?.date || new Date().toISOString().slice(0, 10);
        $('#attendance-status').value = 'present';
        $('#attendance-note').value = '';
        $('#attendance-dialog').showModal();
    }

    async function importCsv(file) {
        if (!file) return;
        const form = new FormData();
        form.append('csv', file);
        try {
            const result = await api('import', { method: 'POST', body: form });
            const errors = result.errors.length ? ` ${result.errors.length} row issue(s): ${result.errors.slice(0, 3).join(' ')}` : '';
            notice(`Imported ${result.created}, skipped ${result.skipped}.${errors}`, result.errors.length > 0);
            await refreshDashboard();
            if (!$('#students-screen').hidden) await loadStudents();
        } catch (error) {
            notice(error.message, true);
        } finally {
            $('#csv-file').value = '';
        }
    }

    function initSettings() {
        const settingsForm = $('#settings-form');
        if (settingsForm) {
            const defaults = {
                school: 'Maria Clara L. Lobregat NHS',
                school_id: '303922',
                division: 'Zamboanga City',
                district: '',
                region: 'Region IX',
                school_year: '2026-2027',
                semester: '',
                grade: 'Grade 10',
                track: '',
                course: '',
                section: 'COMPASSION',
                adviser: '',
                refresh_seconds: '3',
                theme: 'light'
            };
            const saved = JSON.parse(localStorage.getItem('qrcam_settings') || '{}');
            const config = { ...defaults, ...saved };
            for (const [key, val] of Object.entries(config)) {
                if (settingsForm.elements[key]) settingsForm.elements[key].value = val;
            }
            if (config.theme) applyTheme(config.theme);
            resetRefreshInterval(parseInt(config.refresh_seconds, 10));

            settingsForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const fd = new FormData(settingsForm);
                const obj = {};
                fd.forEach((v, k) => { obj[k] = v; });
                localStorage.setItem('qrcam_settings', JSON.stringify(obj));
                $('#settings-status').textContent = 'Settings saved.';
                setTimeout(() => { $('#settings-status').textContent = ''; }, 3000);
                if (obj.theme) applyTheme(obj.theme);
                resetRefreshInterval(parseInt(obj.refresh_seconds, 10));
            });

            $('#reset-settings')?.addEventListener('click', () => {
                localStorage.removeItem('qrcam_settings');
                for (const [key, val] of Object.entries(defaults)) {
                    if (settingsForm.elements[key]) settingsForm.elements[key].value = val;
                }
                $('#settings-status').textContent = 'Defaults restored.';
                setTimeout(() => { $('#settings-status').textContent = ''; }, 3000);
                applyTheme(defaults.theme);
                resetRefreshInterval(parseInt(defaults.refresh_seconds, 10));
            });
        }

        $('#teacher-create-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.currentTarget;
            const fd = new FormData(form);
            const statusEl = $('#teacher-management-status');
            statusEl.textContent = '';
            statusEl.classList.remove('error');
            try {
                await api('create_teacher', {
                    method: 'POST',
                    body: JSON.stringify({
                        full_name: fd.get('full_name'),
                        username: fd.get('username'),
                        password: fd.get('password'),
                    }),
                });
                form.reset();
                statusEl.textContent = 'Teacher account created successfully.';
                await loadTeacherAccounts();
            } catch (err) {
                statusEl.textContent = err.message;
                statusEl.classList.add('error');
            }
        });

        $('#my-account-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.currentTarget;
            const fd = new FormData(form);
            const statusEl = $('#my-account-status');
            statusEl.textContent = '';
            statusEl.classList.remove('error');
            try {
                const res = await api('update_my_account', {
                    method: 'POST',
                    body: JSON.stringify({
                        full_name: fd.get('full_name'),
                        username: fd.get('username'),
                        current_password: fd.get('current_password'),
                        new_password: fd.get('new_password') || undefined,
                    }),
                });
                statusEl.textContent = 'Account updated successfully.';
                if (res.adviser) $('#adviser-name').textContent = res.adviser;
                form.elements.current_password.value = '';
                form.elements.new_password.value = '';
            } catch (err) {
                statusEl.textContent = err.message;
                statusEl.classList.add('error');
            }
        });

        document.body.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.edit-teacher');
            if (editBtn) {
                const dialog = $('#teacher-edit-dialog');
                const form = $('#teacher-edit-form');
                if (dialog && form) {
                    form.elements.id.value = editBtn.dataset.id;
                    form.elements.full_name.value = editBtn.dataset.name;
                    form.elements.username.value = editBtn.dataset.username;
                    form.elements.is_active.checked = editBtn.dataset.active === '1';
                    form.elements.password.value = '';
                    $('#teacher-edit-error').textContent = '';
                    dialog.showModal();
                }
            }
        });

        $('#teacher-edit-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.currentTarget;
            const fd = new FormData(form);
            const errEl = $('#teacher-edit-error');
            errEl.textContent = '';
            try {
                await api('update_teacher', {
                    method: 'POST',
                    body: JSON.stringify({
                        id: fd.get('id'),
                        full_name: fd.get('full_name'),
                        username: fd.get('username'),
                        password: fd.get('password') || undefined,
                        is_active: form.elements.is_active.checked,
                    }),
                });
                $('#teacher-edit-dialog').close();
                notice('Teacher account updated.');
                await loadTeacherAccounts();
            } catch (err) {
                errEl.textContent = err.message;
            }
        });
    }

    function resetRefreshInterval(seconds) {
        if (state.refreshTimer) window.clearInterval(state.refreshTimer);
        if (seconds > 0) {
            state.refreshTimer = window.setInterval(refreshDashboard, seconds * 1000);
        }
    }

    $('#login-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        $('#login-error').textContent = '';
        const loginForm = event.currentTarget;
        const form = new FormData(loginForm);
        try {
            const result = await api('login', {
                method: 'POST',
                body: JSON.stringify({ username: form.get('username'), password: form.get('password') }),
            });
            state.csrf = result.csrf;
            loginForm.reset();
            showAuthenticated(true, result.adviser || form.get('username'), result.role || 'admin', result.section || '');
        } catch (error) {
            $('#login-error').textContent = error.message;
        }
    });

    $('#logout-button').addEventListener('click', async () => {
        try {
            await api('logout', { method: 'POST', body: '{}' });
            const session = await api('bootstrap');
            state.csrf = session.csrf;
            showAuthenticated(false);
        } catch (error) {
            notice(error.message, true);
        }
    });

    document.querySelectorAll('[data-screen]').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();
            const screen = button.dataset.screen;
            setScreen(screen);
            if (screen === 'students') await loadStudents().catch((error) => notice(error.message, true));
        });
    });
    $('#student-search').addEventListener('input', () => renderStudents(state.students));
    $('#open-scanner').addEventListener('click', startScanner);
    $('#scanner-stop').addEventListener('click', stopScanner);
    $('#scanner-dialog').addEventListener('close', stopScanner);
    $('#quick-import').addEventListener('click', () => $('#csv-file').click());
    $('#students-import').addEventListener('click', () => $('#csv-file').click());
    $('#csv-file').addEventListener('change', (event) => importCsv(event.target.files[0]));
    document.body.addEventListener('click', (event) => {
        const qrButton = event.target.closest('.show-qr');
        if (qrButton) openQr(qrButton.dataset.student).catch((error) => notice(error.message, true));
        const adjustButton = event.target.closest('.adjust-attendance');
        if (adjustButton) openAttendance(adjustButton.dataset.student, adjustButton.dataset.date || '');
        const closeButton = event.target.closest('.close-dialog');
        if (closeButton) closeButton.closest('dialog').close();
    });
    $('#attendance-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await api('set_attendance', {
                method: 'POST',
                body: JSON.stringify({
                    student_id: $('#attendance-student').value,
                    date: $('#attendance-date').value,
                    status: $('#attendance-status').value,
                    note: $('#attendance-note').value,
                }),
            });
            $('#attendance-dialog').close();
            notice('Attendance updated and audit entry recorded.');
            await refreshDashboard();
        } catch (error) {
            notice(error.message, true);
        }
    });
    $('#report-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const start = $('#report-start').value;
        const end = $('#report-end').value;
        try {
            const result = await api('report', { query: `&start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}` });
            state.reportRows = result.rows;
            const rowCount = result.rows.length;
            // Update Excel view sheet tab label
            const sheetName = $('#excel-sheet-name');
            if (sheetName) sheetName.textContent = `Attendance ${start} – ${end}`;
            // Update row count badge
            const rowCountEl = $('#report-row-count');
            if (rowCountEl) rowCountEl.textContent = `${rowCount} row${rowCount !== 1 ? 's' : ''}`;
            // Update status bar
            const statusSummary = $('#excel-status-summary');
            if (statusSummary) {
                const presentCount = result.rows.filter(r => r.status === 'present').length;
                const absentCount = result.rows.filter(r => r.status === 'absent').length;
                const excusedCount = result.rows.filter(r => r.status === 'excused').length;
                statusSummary.textContent = `${rowCount} records  ·  Present: ${presentCount}  ·  Absent: ${absentCount}  ·  Excused: ${excusedCount}`;
            }
            // Render rows with row numbers
            $('#report-rows').innerHTML = rowCount ? result.rows.map((row, idx) => `<tr>
                <td class="excel-row-num">${idx + 1}</td>
                <td>${esc(row.date)}</td><td>${esc(row.student_no)}</td><td>${esc(row.full_name)}</td><td>${esc(row.class_section || '—')}</td>
                <td><span class="status-badge ${esc(row.status)}">${esc(row.status)}</span></td><td>${esc(row.attended_at || '—')}</td><td>${esc(row.note || '—')}</td>
                <td><button class="text-button adjust-attendance" data-student="${esc(state.students.find((s) => s.student_no === row.student_no)?.id || '')}" data-date="${esc(row.date)}">Adjust</button></td>
            </tr>`).join('') : '<tr><td colspan="9" class="empty">No attendance records found for this date range.</td></tr>';
            // Show excel view, hide empty state
            const excelView = $('#report-excel-view');
            const emptyState = $('#report-empty-state');
            if (excelView) excelView.hidden = false;
            if (emptyState) emptyState.hidden = true;
            $('#print-report').disabled = rowCount === 0;
        } catch (error) {
            notice(error.message, true);
        }
    });
    $('#print-report').addEventListener('click', () => window.print());
    $('#download-qr').addEventListener('click', () => {
        if (!state.qrStudent) return;
        const link = document.createElement('a');
        link.download = `QRCAM-${state.qrStudent.student_no}.png`;
        link.href = $('#qr-canvas').toDataURL('image/png');
        link.click();
    });
    $('#print-qr').addEventListener('click', () => {
        const student = state.qrStudent;
        if (!student) return;
        const image = $('#qr-canvas').toDataURL('image/png');
        const printWindow = window.open('', '_blank', 'noopener');
        if (!printWindow) {
            notice('Allow pop-ups to print this QR code.', true);
            return;
        }
        printWindow.document.write(`<!doctype html><title>Student QR</title><style>body{font:16px Arial;text-align:center;padding:30px}img{width:280px;height:280px}</style><h1>${esc(student.full_name)}</h1><p>${esc(student.student_no)} · ${esc(student.class_section)}</p><img src="${image}" onload="window.print()"><p>QRCAMv2 attendance code</p>`);
        printWindow.document.close();
    });
    $('#menu-toggle').addEventListener('click', () => $('.sidebar').classList.toggle('open'));
    document.querySelectorAll('.modal').forEach((dialog) => dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    }));

    init();
})();
