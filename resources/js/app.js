import './bootstrap';

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

$$('[data-modal]').forEach(button => button.addEventListener('click', () => {
    const dialog = document.getElementById(button.dataset.modal);
    dialog?.showModal();
    if (dialog?.id === 'scanModal') setTimeout(() => $('#barcodeInput')?.focus(), 80);
}));
$$('.modal-close').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
$$('dialog').forEach(dialog => dialog.addEventListener('click', event => {
    if (event.target === dialog) dialog.close();
}));
$$('.toast button').forEach(button => button.addEventListener('click', () => button.parentElement.remove()));
setTimeout(() => $$('.toast').forEach(toast => toast.remove()), 5000);

const sidebar = $('#sidebar');
const overlay = $('#overlay');
$('#menuButton')?.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
});
overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
});

const barcodeInput = $('#barcodeInput');
let lookupTimer;
const lookupGuest = async () => {
    const code = barcodeInput?.value.trim();
    if (!code || code.length < 8) return;
    const message = $('#guestLookupMessage');
    message.textContent = 'Mencari data tamu...';
    message.className = 'lookup-message loading';
    try {
        const response = await fetch(`/qr-code/${encodeURIComponent(code)}`, {
            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Data tamu tidak ditemukan.');
        const guests = data.guests || [data.guest];
        const batchInputs = $('#registeredGuestIds');
        batchInputs.replaceChildren();
        if (data.kind === 'student' && data.batch) {
            guests.forEach(guest => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'registered_guest_ids[]';
                input.value = guest.id;
                batchInputs.append(input);
            });
        }
        $('#registeredGuestId').value = data.kind === 'student' && !data.batch ? data.guest.id : '';
        $('#institutionalGuestId').value = data.kind === 'institutional' ? data.guest.id : '';
        $('#guestNameInput').value = guests.map(guest => guest.full_name).join(' & ');
        $('#guestTypeInput').value = data.guest.guest_type;
        $('#guestTypeHidden').value = data.guest.guest_type;
        $('#seatNumberInput').value = guests.map(guest => guest.seat_number).filter(Boolean).join(', ');
        message.textContent = data.kind === 'institutional'
            ? `${data.guest.full_name} · Kursi ${data.guest.seat_number} · ${data.position || data.category} · ${data.institution}`
            : `${guests.map(guest => `${guest.full_name} (Kursi ${guest.seat_number})`).join(' & ')} · Tamu ${data.student} · Kuota ${data.quota.used}/${data.quota.total}`;
        message.className = 'lookup-message success';
        $('#guestNameInput').classList.add('auto-filled');
    } catch (error) {
        $('#registeredGuestId').value = '';
        $('#registeredGuestIds').replaceChildren();
        $('#institutionalGuestId').value = '';
        $('#guestNameInput').value = '';
        $('#seatNumberInput').value = '';
        $('#guestTypeHidden').value = '';
        message.textContent = error.message;
        message.className = 'lookup-message error';
    }
};
barcodeInput?.addEventListener('input', () => {
    barcodeInput.value = barcodeInput.value.toUpperCase().replace(/[^A-Z0-9.-]/g, '');
    const scanned = barcodeInput.value.length >= 8;
    $('.hardware-scanner')?.classList.toggle('scanned', scanned);
    if ($('#scannerStatus')) $('#scannerStatus').textContent = scanned ? 'KODE TERBACA' : 'MENUNGGU SCAN';
    clearTimeout(lookupTimer);
    if (scanned) lookupTimer = setTimeout(lookupGuest, 350);
});
barcodeInput?.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        clearTimeout(lookupTimer);
        lookupGuest();
    }
});

$('#passwordToggle')?.addEventListener('click', event => {
    const password = $('#password');
    const visible = password.type === 'text';
    password.type = visible ? 'password' : 'text';
    event.currentTarget.textContent = visible ? '◎' : '◉';
});
