(function () {
    const config = window.ATeamBandroomBooking;
    if (!config) return;

    const modal = document.getElementById('ateam-bandroom-booking');
    const form = document.getElementById('ateam-bandroom-booking-form');
    if (!modal || !form) return;
    const start = form.elements.start_time;
    const end = form.elements.end_time;
    const date = form.elements.date;
    const total = form.querySelector('[data-ateam-booking-total]');
    const hours = form.querySelector('[data-ateam-booking-hours]');
    const message = form.querySelector('.ateam-booking-form__message');
    const interval = Number(config.interval) || 30;

    function minutes(time) { const bits = time.split(':'); return Number(bits[0]) * 60 + Number(bits[1]); }
    function label(value) { const bits = value.split(':'); let hour = Number(bits[0]); const suffix = hour >= 12 ? 'PM' : 'AM'; hour = hour % 12 || 12; return hour + ':' + bits[1] + ' ' + suffix; }
    function fill(select, selected) {
        select.innerHTML = '';
        for (let value = minutes(config.openingTime); value <= minutes(config.closingTime); value += interval) {
            const time = String(Math.floor(value / 60)).padStart(2, '0') + ':' + String(value % 60).padStart(2, '0');
            const option = new Option(label(time), time, false, time === selected);
            select.add(option);
        }
    }
    function amount(value) { return config.currency + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function calculate() {
        const duration = (minutes(end.value) - minutes(start.value)) / 60;
        if (duration < 1) { hours.textContent = 'Select at least 1 hour'; total.textContent = amount(0); return false; }
        let cost = Number(config.firstHourRate) + (duration - 1) * Number(config.additionalHourRate);
        if (form.elements.recording.checked) cost += duration * Number(config.recordingHourRate);
        if (form.elements.engineer.checked) cost += duration * Number(config.engineerHourRate);
        hours.textContent = duration + (duration === 1 ? ' hour' : ' hours');
        total.textContent = amount(cost);
        return true;
    }
    function open() { modal.classList.add('is-open'); modal.setAttribute('aria-hidden', 'false'); document.body.classList.add('ateam-booking-open'); date.focus(); }
    function close() { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('ateam-booking-open'); }
    fill(start, config.openingTime);
    fill(end, String(Math.min(minutes(config.openingTime) + 60, minutes(config.closingTime)) / 60 | 0).padStart(2, '0') + ':' + String((minutes(config.openingTime) + 60) % 60).padStart(2, '0'));
    date.min = new Date().toISOString().slice(0, 10);
    calculate();

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('.ateam-bandroom-booking-trigger, [data-ateam-bandroom-booking], a[href="#ateam-bandroom-booking"]');
        if (trigger) { event.preventDefault(); open(); }
        if (event.target.closest('[data-ateam-booking-close]')) close();
    });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') close(); });
    form.addEventListener('change', calculate);
    form.addEventListener('submit', function (event) {
        event.preventDefault(); message.textContent = '';
        if (!calculate()) { message.textContent = 'Please choose a session of at least one hour.'; return; }
        const button = form.querySelector('button[type="submit"]'); button.disabled = true; button.textContent = 'Sending...';
        const data = new FormData(form); data.append('action', 'ateam_bandroom_submit_booking'); data.append('nonce', config.nonce);
        fetch(config.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
            .then(response => response.json()).then(response => {
                message.textContent = response.data && response.data.message ? response.data.message : 'Unable to send your enquiry.';
                message.classList.toggle('is-error', !response.success);
                if (response.success) { form.reset(); fill(start, config.openingTime); fill(end, String(Math.floor((minutes(config.openingTime) + 60) / 60)).padStart(2, '0') + ':' + String((minutes(config.openingTime) + 60) % 60).padStart(2, '0')); calculate(); }
            }).catch(() => { message.textContent = 'Unable to send your enquiry. Please try again.'; message.classList.add('is-error'); })
            .finally(() => { button.disabled = false; button.textContent = 'Send Booking Enquiry'; });
    });
})();
