const dateInput = document.getElementById('appointment_date');
const mechanicSelect = document.getElementById('mechanic_id');
const availabilityBox = document.getElementById('availability');
const form = document.getElementById('appointmentForm');
const messageBox = document.getElementById('message');

function showMessage(type, text) {
    messageBox.className = `message ${type}`;
    messageBox.textContent = text;
}

function loadMechanicAvailability() {
    const date = dateInput.value;

    if (!date) {
        mechanicSelect.disabled = true;
        availabilityBox.textContent =
            'Select an appointment date to see mechanic availability.';
        return;
    }

    mechanicSelect.disabled = true;
    mechanicSelect.innerHTML = '<option>Loading availability...</option>';
    availabilityBox.textContent = 'Checking available slots...';

    fetch(`api/mechanics.php?date=${encodeURIComponent(date)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Could not load availability.');
            }

            mechanicSelect.innerHTML = '';
            let availableCount = 0;

            data.mechanics.forEach(mechanic => {
                const option = document.createElement('option');
                option.value = mechanic.mechanic_id;

                if (Number(mechanic.free_slots) > 0) {
                    option.textContent =
                        `${mechanic.mechanic_name} — ${mechanic.free_slots} slot(s) available`;
                    availableCount++;
                } else {
                    option.textContent =
                        `${mechanic.mechanic_name} — FULL`;
                    option.disabled = true;
                }

                mechanicSelect.appendChild(option);
            });

            mechanicSelect.disabled = availableCount === 0;

            availabilityBox.textContent = availableCount
                ? 'Select one of the mechanics with available slots.'
                : 'All mechanics are fully booked for this date.';
        })
        .catch(error => {
            mechanicSelect.disabled = true;
            mechanicSelect.innerHTML =
                '<option>Unable to load mechanics</option>';
            availabilityBox.textContent = error.message;
        });
}

dateInput.addEventListener('change', loadMechanicAvailability);

form.addEventListener('submit', event => {
    const phone = document.getElementById('phone').value.trim();
    const engine = document.getElementById('engine_number').value.trim();

    if (!/^[0-9]{10,15}$/.test(phone)) {
        event.preventDefault();
        showMessage('error', 'Phone number must contain 10 to 15 digits.');
        return;
    }

    if (!/^[A-Za-z0-9\-\/]+$/.test(engine)) {
        event.preventDefault();
        showMessage('error',
            'Engine number may contain only letters, numbers, hyphens, and slashes.');
        return;
    }

    if (!mechanicSelect.value) {
        event.preventDefault();
        showMessage('error', 'Please select an available mechanic.');
    }
});

const params = new URLSearchParams(window.location.search);
if (params.has('message')) {
    showMessage(params.get('type') === 'error' ? 'error' : 'success',
        params.get('message'));
}
