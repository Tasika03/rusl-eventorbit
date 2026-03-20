document.addEventListener('DOMContentLoaded', function () {

    // 1. EVENTS PAGE – Modal popup
    var viewBtns = document.querySelectorAll('.viewBtn');
    viewBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').textContent       = btn.dataset.title;
            document.getElementById('modalDescription').textContent = btn.dataset.description;
            document.getElementById('modalDate').textContent        = btn.dataset.date;
            document.getElementById('modalLocation').textContent    = btn.dataset.location;
            document.getElementById('modalFaculty').textContent     = btn.dataset.faculty;
            document.getElementById('modalCategory').textContent    = btn.dataset.category;
            document.getElementById('modalImg').src                 = btn.dataset.img;
            document.getElementById('modalImg').alt                 = btn.dataset.title;

            var accessEl  = document.getElementById('modalAccess');
            var accessVal = btn.dataset.access;
            if (accessEl) {
                if (accessVal === 'Public') {
                    accessEl.innerHTML = '<span style="background:#198754;color:#fff;padding:3px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">🌐 Public</span>';
                } else {
                    accessEl.innerHTML = '<span style="background:#6c757d;color:#fff;padding:3px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">🔒 Private</span>';
                }
            }
            var modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        });
    });

    // 2. EVENTS PAGE – Search & Filter
    var searchInput    = document.getElementById('searchInput');
    var categoryFilter = document.getElementById('categoryFilter');
    var facultyFilter  = document.getElementById('facultyFilter');
    var filterBtn      = document.getElementById('filterBtn');
    var noResults      = document.getElementById('noResults');

    function filterEvents() {
        var searchVal   = searchInput   ? searchInput.value.toLowerCase().trim() : '';
        var categoryVal = categoryFilter ? categoryFilter.value : 'all';
        var facultyVal  = facultyFilter  ? facultyFilter.value  : 'all';
        var cards       = document.querySelectorAll('.event-card');
        var visibleCount = 0;

        cards.forEach(function (card) {
            var title    = card.dataset.title.toLowerCase();
            var category = card.dataset.category;
            var faculty  = card.dataset.faculty;

            var matchSearch   = title.includes(searchVal);
            var matchCategory = (categoryVal === 'all') || (category === categoryVal);
            var matchFaculty  = (facultyVal  === 'all') || (faculty  === facultyVal);

            if (matchSearch && matchCategory && matchFaculty) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResults) {
            noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }

    if (filterBtn)      filterBtn.addEventListener('click', filterEvents);
    if (searchInput)    searchInput.addEventListener('keyup', filterEvents);
    if (categoryFilter) categoryFilter.addEventListener('change', filterEvents);
    if (facultyFilter)  facultyFilter.addEventListener('change', filterEvents);

    var urlParams   = new URLSearchParams(window.location.search);
    var urlCategory = urlParams.get('category');
    if (urlCategory && categoryFilter) {
        categoryFilter.value = urlCategory;
        filterEvents();
    }

    // Auto-open modal from URL hash
    var urlHash = window.location.hash.replace('#', '');
    if (urlHash) {
        var targetCard = document.getElementById(urlHash);
        if (targetCard) {
            setTimeout(function () {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var hashBtn = targetCard.querySelector('.viewBtn');
                if (hashBtn) hashBtn.click();
            }, 400);
        }
    }

    // 3. ADD EVENT PAGE – character counter and image preview only
    // NO e.preventDefault — form submits normally to add-event-handler.php
    var descArea  = document.getElementById('eventDescription');
    var charCount = document.getElementById('charCount');

    if (descArea && charCount) {
        descArea.addEventListener('input', function () {
            var len = descArea.value.length;
            charCount.textContent = len;
            if (len > 500) {
                descArea.value        = descArea.value.substring(0, 500);
                charCount.textContent = 500;
            }
        });
    }

    var imageInput   = document.getElementById('eventImage');
    var imagePreview = document.getElementById('imagePreview');
    var previewWrap  = document.getElementById('imagePreviewWrapper');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            var file = imageInput.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image file is too large. Maximum size is 5MB.');
                    imageInput.value = '';
                    if (previewWrap) previewWrap.style.display = 'none';
                    return;
                }
                var reader    = new FileReader();
                reader.onload = function (e) {
                    if (imagePreview) imagePreview.src          = e.target.result;
                    if (previewWrap)  previewWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                if (previewWrap) previewWrap.style.display = 'none';
            }
        });
    }

    var resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (charCount)   charCount.textContent    = '0';
            if (previewWrap) previewWrap.style.display = 'none';
        });
    }

    // 4. AUTH NAVBAR – cookie based login/logout display
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }

    var eoUser       = getCookie('eo_user');
    var navLogin     = document.getElementById('nav-login');
    var navRegister  = document.getElementById('nav-register');
    var navDashboard = document.getElementById('nav-dashboard');
    var navLogout    = document.getElementById('nav-logout');
    var navUsername  = document.getElementById('nav-username');

    if (eoUser) {
        if (navLogin)     navLogin.style.display     = 'none';
        if (navRegister)  navRegister.style.display  = 'none';
        if (navDashboard) navDashboard.style.display = '';
        if (navLogout)    navLogout.style.display    = '';
        if (navUsername)  navUsername.textContent    = eoUser;
    } else {
        if (navLogin)     navLogin.style.display     = '';
        if (navRegister)  navRegister.style.display  = '';
        if (navDashboard) navDashboard.style.display = 'none';
        if (navLogout)    navLogout.style.display    = 'none';
    }

    // 5. SHOW PHP HANDLER MESSAGES via URL params
    var params  = new URLSearchParams(window.location.search);
    var status  = params.get('status');
    var msg     = params.get('msg');
    var evtName = params.get('name');

    var contactMsg = document.getElementById('contactFormMessage');
    if (contactMsg) {
        if (status === 'success') {
            contactMsg.style.display = 'block';
            contactMsg.innerHTML = '<div class="alert alert-success">✅ Your message has been sent successfully!</div>';
        } else if (status === 'error' && msg) {
            var errItems = decodeURIComponent(msg).split('|').map(function(e){ return '<li>'+e+'</li>'; }).join('');
            contactMsg.style.display = 'block';
            contactMsg.innerHTML = '<div class="alert alert-danger"><ul class="mb-0">'+errItems+'</ul></div>';
        }
    }

        var formMsg = document.getElementById('formMessage');
        var topMsg  = document.getElementById('topMessage');

        if (status === 'success' && evtName) {
            var successHtml = '<div class="alert alert-success mt-2">✅ Event "<strong>' + decodeURIComponent(evtName) + '</strong>" submitted successfully! It will appear on the events page after review.</div>';
            if (topMsg)  topMsg.innerHTML = successHtml;
            if (formMsg) { formMsg.style.display = 'block'; formMsg.innerHTML = successHtml; }
            // Scroll to top message
            if (topMsg) window.scrollTo({ top: 0, behavior: 'smooth' });

        } else if (status === 'error' && msg) {
            var errItems2 = decodeURIComponent(msg).split('|').map(function(e) {
                return '<li>' + e + '</li>';
            }).join('');
            var errorHtml = '<div class="alert alert-danger mt-2"><ul class="mb-0">' + errItems2 + '</ul></div>';
            if (topMsg)  topMsg.innerHTML = errorHtml;
            if (formMsg) { formMsg.style.display = 'block'; formMsg.innerHTML = errorHtml; }
            if (topMsg) window.scrollTo({ top: 0, behavior: 'smooth' });
        }
});