const apiBase = '/api/v1';

const tokenKey = 'bridgeedu_api_token';
const userKey = 'bridgeedu_user';

const elements = {
    loginForm: document.querySelector('#login-form'),
    logoutButton: document.querySelector('#logout-button'),
    authStatus: document.querySelector('#auth-status'),
    mentorList: document.querySelector('#mentor-list'),
    requestList: document.querySelector('#request-list'),
    createRequestForm: document.querySelector('#create-request-form'),
    requestTopic: document.querySelector('#topic_of_interest'),
    messageBox: document.querySelector('#message-box'),
    currentUserName: document.querySelector('#current-user-name'),
    currentUserRole: document.querySelector('#current-user-role'),
};

function showMessage(message, tone = 'info') {
    if (!elements.messageBox) {
        return;
    }

    elements.messageBox.textContent = message;
    elements.messageBox.classList.remove('hidden');
    elements.messageBox.className = `rounded-md border px-4 py-3 text-sm ${tone === 'error' ? 'border-red-200 bg-red-50 text-red-700' : tone === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-sky-200 bg-sky-50 text-sky-700'}`;
}

function getToken() {
    return localStorage.getItem(tokenKey);
}

function setToken(token) {
    if (token) {
        localStorage.setItem(tokenKey, token);
        return;
    }

    localStorage.removeItem(tokenKey);
}

function getUser() {
    const raw = localStorage.getItem(userKey);
    return raw ? JSON.parse(raw) : null;
}

function setUser(user) {
    if (user) {
        localStorage.setItem(userKey, JSON.stringify(user));
        return;
    }

    localStorage.removeItem(userKey);
}

function authHeaders() {
    const token = getToken();
    return token ? { Authorization: `Bearer ${token}` } : {};
}

async function apiFetch(path, options = {}) {
    const headers = {
        'Content-Type': 'application/json',
        ...(options.headers || {}),
        ...authHeaders(),
    };

    const response = await fetch(`${apiBase}${path}`, {
        ...options,
        headers,
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(payload.message || 'Request failed.');
    }

    return payload;
}

function renderAuthState() {
    const user = getUser();

    if (!user) {
        elements.authStatus.textContent = 'Not logged in';
        elements.currentUserName.textContent = 'Guest';
        elements.currentUserRole.textContent = 'Public';
        elements.logoutButton?.classList.add('hidden');
        return;
    }

    elements.authStatus.textContent = 'Authenticated';
    elements.currentUserName.textContent = user.full_name || user.name;
    elements.currentUserRole.textContent = user.role;
    elements.logoutButton?.classList.remove('hidden');
}

function renderMentors(mentors) {
    if (!Array.isArray(mentors)) {
        return;
    }

    elements.mentorList.innerHTML = mentors.map((mentor) => `
        <div class="rounded-lg border border-slate-200 p-4">
            <div class="font-semibold text-slate-900">${mentor.full_name || mentor.name}</div>
            <div class="text-sm text-slate-600">${mentor.email}</div>
            <div class="text-xs text-slate-500 mt-2">District: ${mentor.district || 'N/A'}</div>
        </div>
    `).join('');
}

function renderRequests(requests) {
    if (!Array.isArray(requests)) {
        return;
    }

    elements.requestList.innerHTML = requests.map((request) => `
        <div class="rounded-lg border border-slate-200 p-4 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <div class="font-semibold text-slate-900">${request.topic_of_interest}</div>
                    <div class="text-sm text-slate-600">Student: ${request.student?.full_name || 'Unknown'}</div>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">${request.status}</span>
            </div>
            ${request.mentor ? `<div class="text-sm text-slate-600">Mentor: ${request.mentor.full_name || request.mentor.name}</div>` : ''}
            <button data-request-id="${request.id}" class="claim-button rounded-md bg-sky-600 px-3 py-2 text-sm font-semibold text-white ${getUser()?.role !== 'mentor' || request.status !== 'pending' ? 'hidden' : ''}">Claim request</button>
        </div>
    `).join('');

    elements.requestList.querySelectorAll('.claim-button').forEach((button) => {
        button.addEventListener('click', async () => {
            const requestId = button.dataset.requestId;
            try {
                const response = await apiFetch(`/mentorship-requests/${requestId}`, {
                    method: 'PATCH',
                    body: JSON.stringify({}),
                });

                await loadRequests();
                showMessage(`Request ${requestId} has been claimed successfully.`, 'success');
            } catch (error) {
                showMessage(error.message, 'error');
            }
        });
    });
}

async function loadMentors() {
    try {
        const response = await apiFetch('/mentors');
        renderMentors(response.data || []);
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

async function loadRequests() {
    try {
        const response = await apiFetch('/mentorship-requests');
        renderRequests(response.data || []);
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

async function loadDashboard() {
    renderAuthState();

    if (!getToken()) {
        elements.mentorList.innerHTML = '<div class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Login to load mentor data.</div>';
        elements.requestList.innerHTML = '<div class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Login to load mentorship requests.</div>';
        return;
    }

    await loadMentors();
    await loadRequests();
}

async function handleLogin(event) {
    event.preventDefault();

    const email = document.querySelector('#email').value.trim();
    const password = document.querySelector('#password').value.trim();

    try {
        const response = await fetch(`${apiBase}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password }),
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message || 'Login failed.');
        }

        setToken(payload.token);
        setUser(payload.user);
        renderAuthState();
        await loadDashboard();
        showMessage('Logged in successfully.', 'success');
        elements.loginForm.reset();
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

async function handleLogout() {
    try {
        await apiFetch('/auth/logout', {
            method: 'POST',
        });
    } catch (error) {
        // The backend may still reject after token expiry; we clear UI anyway.
    }

    setToken(null);
    setUser(null);
    renderAuthState();
    await loadDashboard();
    showMessage('Logged out successfully.');
}

async function handleCreateRequest(event) {
    event.preventDefault();

    const topic = elements.requestTopic.value.trim();

    try {
        await apiFetch('/mentorship-requests', {
            method: 'POST',
            body: JSON.stringify({ topic_of_interest: topic }),
        });

        elements.createRequestForm.reset();
        await loadRequests();
        showMessage('Mentorship request created successfully.', 'success');
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (elements.loginForm) {
        elements.loginForm.addEventListener('submit', handleLogin);
    }

    if (elements.logoutButton) {
        elements.logoutButton.addEventListener('click', handleLogout);
    }

    if (elements.createRequestForm) {
        elements.createRequestForm.addEventListener('submit', handleCreateRequest);
    }

    loadDashboard();
});

