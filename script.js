document.addEventListener('DOMContentLoaded', function () {

    //1. EVENTS PAGE – Modal popup for event details
    const viewBtns = document.querySelectorAll('.viewBtn');

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

            //  Public/Private badge
            var accessEl = document.getElementById('modalAccess');
            var accessVal = btn.dataset.access;
            if (accessVal === 'Public') {
                accessEl.innerHTML = '<span style="background:#198754;color:#fff;padding:3px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">🌐 Public</span>';
            } else {
                accessEl.innerHTML = '<span style="background:#6c757d;color:#fff;padding:3px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">🔒 Private</span>';
            }

            // Show Bootstrap modal
            var modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        });
    });


    // 2. EVENTS PAGE – Search & Filter functionality
    const searchInput    = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const facultyFilter  = document.getElementById('facultyFilter');
    const filterBtn      = document.getElementById('filterBtn');
    const noResults      = document.getElementById('noResults');

    function filterEvents() {
        const searchVal   = searchInput   ? searchInput.value.toLowerCase().trim() : '';
        const categoryVal = categoryFilter ? categoryFilter.value : 'all';
        const facultyVal  = facultyFilter  ? facultyFilter.value  : 'all';

        const cards = document.querySelectorAll('.event-card');
        let visibleCount = 0;

        cards.forEach(function (card) {
            const title    = card.dataset.title.toLowerCase();
            const category = card.dataset.category;
            const faculty  = card.dataset.faculty;

            const matchSearch   = title.includes(searchVal);
            const matchCategory = (categoryVal === 'all') || (category === categoryVal);
            const matchFaculty  = (facultyVal  === 'all') || (faculty  === facultyVal);

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

    const urlParams = new URLSearchParams(window.location.search);
    const urlCategory = urlParams.get('category');
    if (urlCategory && categoryFilter) {
        categoryFilter.value = urlCategory;
        filterEvents();
    }

    // Auto-open modal 
    const urlHash = window.location.hash.replace('#', '');
    if (urlHash) {
        var targetCard = document.getElementById(urlHash);
        if (targetCard) {
            
            setTimeout(function () {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var btn = targetCard.querySelector('.viewBtn');
                if (btn) btn.click();
            }, 400);
        }
    }


    // 3. ADD EVENT PAGE – Form validation & submission
    const eventForm   = document.getElementById('eventForm');
    const formMessage = document.getElementById('formMessage');
    const descArea    = document.getElementById('eventDescription');
    const charCount   = document.getElementById('charCount');

    if (descArea && charCount) {
        descArea.addEventListener('input', function () {
            const len = descArea.value.length;
            charCount.textContent = len;
            if (len > 500) {
                descArea.value = descArea.value.substring(0, 500);
                charCount.textContent = 500;
            }
        });
    }

    // Image preview
    const imageInput   = document.getElementById('eventImage');
    const imagePreview = document.getElementById('imagePreview');
    const previewWrap  = document.getElementById('imagePreviewWrapper');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files[0];
            if (file) {
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image file is too large. Maximum size is 5MB.');
                    imageInput.value = '';
                    previewWrap.style.display = 'none';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    previewWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewWrap.style.display = 'none';
            }
        });
    }

    // Form submit validation
    if (eventForm) {
        eventForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Check access type radio manually
            const accessType = document.querySelector('input[name="accessType"]:checked');
            const accessError = document.getElementById('accessTypeError');
            if (!accessType) {
                if (accessError) {
                    accessError.textContent = 'Please select an access type.';
                    accessError.style.display = 'block';
                }
                eventForm.classList.add('was-validated');
                return;
            } else {
                if (accessError) {
                    accessError.style.display = 'none';
                    accessError.textContent = '';
                }
            }

            if (!eventForm.checkValidity()) {
                eventForm.classList.add('was-validated');
                return;
            }

            // All valid — show success message
            formMessage.style.display = 'block';
            formMessage.innerHTML =
                '<div class="alert alert-success">' +
                '✅ <strong>Event submitted successfully!</strong> ' +
                'Your event "' + document.getElementById('eventName').value + '" has been received.' +
                '</div>';

            // Reset form
            eventForm.reset();
            eventForm.classList.remove('was-validated');
            if (accessError) { accessError.style.display = 'none'; accessError.textContent = ''; }
            if (charCount)   charCount.textContent = '0';
            if (previewWrap) previewWrap.style.display = 'none';

            formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        // Clear button also resets validation state
        const resetBtn = document.getElementById('resetBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                eventForm.classList.remove('was-validated');
                const accessError = document.getElementById('accessTypeError');
                if (formMessage) { formMessage.style.display = 'none'; formMessage.innerHTML = ''; }
                if (accessError) { accessError.style.display = 'none'; accessError.textContent = ''; }
                if (charCount)   charCount.textContent = '0';
                if (previewWrap) previewWrap.style.display = 'none';
            });
        }
    }


    //  4. CONTACT PAGE – Contact form validation
    const contactForm    = document.getElementById('contactForm');
    const contactMessage = document.getElementById('contactFormMessage');

    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!contactForm.checkValidity()) {
                contactForm.classList.add('was-validated');
                return;
            }

            const name = document.getElementById('contactName').value;

            contactMessage.style.display = 'block';
            contactMessage.innerHTML =
                '<div class="alert alert-success">' +
                '✅ Thank you, <strong>' + name + '</strong>! Your message has been sent. ' +
                'We will get back to you shortly.' +
                '</div>';

            contactForm.reset();
            contactForm.classList.remove('was-validated');
            contactMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

});