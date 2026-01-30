document.addEventListener('DOMContentLoaded', () => {
    
    const detectedTz = (Intl.DateTimeFormat().resolvedOptions().timeZone || '').trim();

    const tzHidden = document.getElementById('timezone');
    const tzDisplay = document.getElementById('timezone_display');

    if (tzHidden && detectedTz) tzHidden.value = detectedTz;

   
    if (tzDisplay && detectedTz) {
        const has = Array.from(tzDisplay.options).some(o => o.value === detectedTz);
        if (has) tzDisplay.value = detectedTz;
    }

    
    if (tzDisplay) {
        tzDisplay.addEventListener('change', () => {
            if (!tzHidden) return;
            const chosen = (tzDisplay.value || '').trim();
            tzHidden.value = chosen || detectedTz || '';
        });
    }

    
    const btnLoc = document.getElementById('btnUseLocation');
    if (btnLoc) btnLoc.addEventListener('click', useMyLocation);
});

const setLocationStatus = (msg) => {
    const el = document.getElementById('locationStatus');
    if (el) el.textContent = msg || '';
};

function useMyLocation() {
    if (!navigator.geolocation) {
        setLocationStatus('Geolocation is not supported by this browser.');
        return;
    }

    setLocationStatus('Requesting location permission...');

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            const latEl = document.getElementById('lat');
            const lngEl = document.getElementById('lng');
            const labelEl = document.getElementById('location_label');

            if (latEl) latEl.value = lat;
            if (lngEl) lngEl.value = lng;

            if (labelEl) labelEl.value = `Lat ${lat.toFixed(5)}, Lng ${lng.toFixed(5)}`;

            setLocationStatus(`Saved: ${lat.toFixed(5)}, ${lng.toFixed(5)}`);
        },
        (err) => {
            setLocationStatus(err?.message || 'Could not get location.');
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 60000
        }
    );
}

const CreateUser = (event) => {
    event.preventDefault();
    const form = event.currentTarget;

    
    const tzHidden = document.getElementById('timezone');
    const tzDisplay = document.getElementById('timezone_display');
    if (tzHidden && !tzHidden.value && tzDisplay && tzDisplay.value) {
        tzHidden.value = tzDisplay.value;
    }

    const fd = new FormData(form);

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 201) {
            alert('User created successfully');
            window.location.href = './Login.php';
        }
        else if (this.readyState === 4 && this.status === 422) {
            const errors = JSON.parse(this.responseText);
            ErrorShowing(errors);
        }
        else if (this.readyState === 4 && this.status === 500 && (this.responseText.includes("Duplicate entry") && this.responseText.includes("for key 'email'"))) {
            ErrorShowing({ emailErr: 'This email already exits' });
        }
        else if (this.readyState === 4 && this.status === 500) {
            alert('server is down: ' + this.responseText);
        }
    }

    xhttp.open('post', '../api/registration.php', true);
    xhttp.send(fd);
}

const ErrorShowing = (errors) => {
    document.getElementById('emptyFieldsErr').textContent = errors.emptyFieldsErr ?? '';
    document.getElementById('nameErr').textContent = errors.nameErr ?? '';
    document.getElementById('emailErr').textContent = errors.emailErr ?? '';
    document.getElementById('passwordErr').textContent = errors.passwordErr ?? '';
    document.getElementById('timezoneErr').textContent = errors.timezoneErr ?? '';
    document.getElementById('locationErr').textContent = errors.locationErr ?? '';
}
