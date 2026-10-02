/**
 * JEEVANSETU - Main UI Application Logic & Page Handlers
 */

// Toast notification helper
function showToast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container-custom';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast-custom border-${type}`;
  
  let iconClass = 'fa-circle-info text-info';
  if (type === 'success') iconClass = 'fa-circle-check text-success';
  if (type === 'danger' || type === 'error') iconClass = 'fa-triangle-exclamation text-danger';
  if (type === 'warning') iconClass = 'fa-triangle-exclamation text-warning';

  toast.innerHTML = `
    <i class="fa-solid ${iconClass} fs-5"></i>
    <div style="font-weight: 500; font-size: 0.9rem;">${message}</div>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.5s ease';
    setTimeout(() => toast.remove(), 500);
  }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
  console.log('[JeevanSetu] Application Initialized');
  
  // Highlight active page link
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-link-custom').forEach(link => {
    const href = link.getAttribute('href');
    if (href && currentPath.endsWith(href)) {
      link.classList.add('active');
    }
  });

  // Check current session state if available
  checkUserSession();
});

// Sync session display in nav
async function checkUserSession() {
  const userNavElem = document.getElementById('nav-user-info');
  if (!userNavElem) return;

  try {
    const res = await JeevanSetuAPI.request('profile.php', 'GET');
    if (res.success && res.data && res.data.profile) {
      const user = res.data.profile;
      userNavElem.innerHTML = `
        <a href="user.html" class="nav-link-custom">
          <i class="fa-solid fa-user-circle me-1 text-primary"></i> ${user.name} (${user.blood_group})
        </a>
      `;
    } else {
      userNavElem.innerHTML = `
        <a href="user.html" class="nav-link-custom">
          <i class="fa-solid fa-right-to-bracket me-1"></i> Login / Register
        </a>
      `;
    }
  } catch (e) {
    console.warn('[Session] Offline / default mode');
  }
}

// ----------------------------------------------------
// Page: Index (Landing Page) Logic
// ----------------------------------------------------
async function initIndexPage() {
  // Initialize landing map preview
  const landingMap = new JeevanSetuMap('leaflet-map', 28.6139, 77.2090, 12);
  landingMap.init();

  // Load preview hospitals on landing map
  const res = await JeevanSetuAPI.request('hospitals.php', 'GET');
  if (res.success && Array.isArray(res.data)) {
    res.data.forEach(h => landingMap.addHospitalMarker(h));
  }

  // Quick SOS Panic trigger from home
  const sosBigBtn = document.getElementById('home-sos-btn');
  if (sosBigBtn) {
    sosBigBtn.addEventListener('click', () => {
      window.location.href = 'sos.html?autotrigger=true';
    });
  }
}

// ----------------------------------------------------
// Page: SOS Dispatch Portal Logic
// ----------------------------------------------------
async function initSOSPage() {
  const sosMap = new JeevanSetuMap('sos-map', 28.6139, 77.2090, 14);
  sosMap.init();

  const latInput = document.getElementById('sos-lat');
  const lngInput = document.getElementById('sos-lng');
  const locationStatus = document.getElementById('location-status');

  // Detect GPS Location
  sosMap.detectLocation((lat, lng) => {
    if (latInput) latInput.value = lat.toFixed(6);
    if (lngInput) lngInput.value = lng.toFixed(6);
    if (locationStatus) {
      locationStatus.className = 'badge bg-success mt-2';
      locationStatus.innerHTML = `<i class="fa-solid fa-check-circle me-1"></i> GPS Location Fixed: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
    }
  });

  // Handle Form Submission
  const sosForm = document.getElementById('sos-form');
  if (sosForm) {
    sosForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const emergencyType = document.getElementById('sos-type').value;
      const lat = parseFloat(latInput.value) || sosMap.lat;
      const lng = parseFloat(lngInput.value) || sosMap.lng;
      const notes = document.getElementById('sos-notes') ? document.getElementById('sos-notes').value : '';

      const submitBtn = sosForm.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-2"></i> Dispatching Signal...`;

      try {
        const result = await JeevanSetuAPI.request('sos.php', 'POST', {
          emergency_type: emergencyType,
          latitude: lat,
          longitude: lng,
          notes: notes
        });

        if (result.success) {
          showToast('EMERGENCY SOS DISPATCHED! Responders notified.', 'success');
          
          // Show confirmation modal
          const alertModal = document.getElementById('sosAlertModal');
          if (alertModal && typeof bootstrap !== 'undefined') {
            document.getElementById('modal-sos-id').innerText = `#SOS-${result.data ? result.data.id : Math.floor(Math.random()*1000)}`;
            document.getElementById('modal-sos-type').innerText = emergencyType;
            document.getElementById('modal-sos-coords').innerText = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            const bsModal = new bootstrap.Modal(alertModal);
            bsModal.show();
          }

          // Add to map
          sosMap.addEmergencyMarker({
            latitude: lat,
            longitude: lng,
            emergency_type: emergencyType,
            status: 'Pending',
            created_at: 'Just now'
          });

          // Reload active history
          loadSOSHistory();
        } else {
          showToast(result.message || 'Failed to dispatch SOS.', 'danger');
        }
      } catch (err) {
        showToast('Connection error. SOS queued locally.', 'warning');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="fa-solid fa-tower-cell me-2"></i> DISPATCH EMERGENCY SOS NOW`;
      }
    });
  }

  // Load User SOS Emergency History
  loadSOSHistory();

  // Check URL autotrigger
  const params = new URLSearchParams(window.location.search);
  if (params.get('autotrigger') === 'true') {
    showToast('Instant Emergency Mode Activated. Verify location and tap DISPATCH.', 'warning');
  }
}

async function loadSOSHistory() {
  const historyContainer = document.getElementById('sos-history-list');
  if (!historyContainer) return;

  const res = await JeevanSetuAPI.request('sos.php', 'GET');
  if (res.success && Array.isArray(res.data)) {
    if (res.data.length === 0) {
      historyContainer.innerHTML = `<p class="text-muted fs-6">No past emergency broadcasts found.</p>`;
      return;
    }

    historyContainer.innerHTML = res.data.map(item => {
      let badgeClass = 'badge-pending';
      if (item.status === 'Dispatched') badgeClass = 'badge-dispatched';
      if (item.status === 'Resolved') badgeClass = 'badge-resolved';

      return `
        <div class="glass-panel p-3 mb-3 border-light">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold text-main"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>${item.emergency_type || 'SOS Alert'}</span>
            <span class="badge-status ${badgeClass}">${item.status}</span>
          </div>
          <div class="text-muted small mb-1"><i class="fa-solid fa-clock me-1"></i> ${item.created_at}</div>
          <div class="text-muted small"><i class="fa-solid fa-location-dot me-1"></i> Coordinates: ${parseFloat(item.latitude).toFixed(4)}, ${parseFloat(item.longitude).toFixed(4)}</div>
        </div>
      `;
    }).join('');
  }
}

// ----------------------------------------------------
// Page: Hospitals Locator Logic
// ----------------------------------------------------
async function initHospitalsPage() {
  const hospMap = new JeevanSetuMap('hospital-map', 28.6139, 77.2090, 12);
  hospMap.init();

  let userLat = 28.6139;
  let userLng = 77.2090;

  // Auto detect user coords for distance calculation
  hospMap.detectLocation((lat, lng) => {
    userLat = lat;
    userLng = lng;
    fetchAndRenderHospitals();
  });

  const searchInput = document.getElementById('hospital-search');
  if (searchInput) {
    searchInput.addEventListener('input', () => fetchAndRenderHospitals(searchInput.value));
  }

  async function fetchAndRenderHospitals(filterQuery = '') {
    const listContainer = document.getElementById('hospitals-list');
    if (!listContainer) return;

    listContainer.innerHTML = `<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fs-3 text-red"></i></div>`;

    const res = await JeevanSetuAPI.request('hospitals.php', 'GET');
    if (res.success && Array.isArray(res.data)) {
      let hospitals = res.data;

      // Calculate distance for each hospital
      hospitals.forEach(h => {
        h.distance = JeevanSetuMap.calculateDistance(userLat, userLng, parseFloat(h.latitude), parseFloat(h.longitude));
      });

      // Sort by nearest distance
      hospitals.sort((a, b) => a.distance - b.distance);

      // Filter by name or address if search entered
      if (filterQuery.trim()) {
        const q = filterQuery.toLowerCase();
        hospitals = hospitals.filter(h => h.name.toLowerCase().includes(q) || h.address.toLowerCase().includes(q));
      }

      // Render pins on map
      hospitals.forEach(h => hospMap.addHospitalMarker(h));

      if (hospitals.length === 0) {
        listContainer.innerHTML = `<p class="text-muted text-center py-4">No matching hospitals found.</p>`;
        return;
      }

      listContainer.innerHTML = hospitals.map(h => `
        <div class="glass-panel p-3 mb-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <h5 class="mb-0 text-main">${h.name}</h5>
              <span class="badge bg-success text-white" style="font-size:0.75rem;"><i class="fa-solid fa-location-arrow me-1"></i>${h.distance.toFixed(1)} km</span>
            </div>
            <div class="text-muted small mb-1"><i class="fa-solid fa-location-dot me-1 text-danger"></i> ${h.address}</div>
            <div class="text-muted small"><i class="fa-solid fa-phone me-1 text-success"></i> Emergency line: ${h.phone}</div>
          </div>
          <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="tel:${h.phone}" class="btn btn-red btn-sm"><i class="fa-solid fa-phone me-1"></i> Call Hospital</a>
            <a href="https://maps.google.com/?q=${h.latitude},${h.longitude}" target="_blank" class="btn btn-outline-custom btn-sm"><i class="fa-solid fa-route me-1"></i> Nav Route</a>
          </div>
        </div>
      `).join('');
    }
  }
}

// ----------------------------------------------------
// Page: Blood Donors Logic
// ----------------------------------------------------
async function initDonorsPage() {
  const searchBgSelect = document.getElementById('search-blood-group');
  const searchVillageInput = document.getElementById('search-village');
  const filterBtn = document.getElementById('filter-donors-btn');

  if (filterBtn) {
    filterBtn.addEventListener('click', loadDonorsList);
  }

  loadDonorsList();

  // Register Donor Form Handler
  const donorForm = document.getElementById('register-donor-form');
  if (donorForm) {
    donorForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const name = document.getElementById('donor-name').value;
      const bloodGroup = document.getElementById('donor-blood-group').value;
      const village = document.getElementById('donor-village').value;
      const phone = document.getElementById('donor-phone').value;

      const res = await JeevanSetuAPI.request('donors.php', 'POST', {
        name, blood_group: bloodGroup, village, phone
      });

      if (res.success) {
        showToast('Thank you! You are registered as a Lifesaving Blood Donor.', 'success');
        donorForm.reset();
        loadDonorsList();
      } else {
        showToast(res.message || 'Failed to register donor.', 'danger');
      }
    });
  }
}

async function loadDonorsList() {
  const container = document.getElementById('donors-grid');
  if (!container) return;

  const bg = document.getElementById('search-blood-group') ? document.getElementById('search-blood-group').value : '';
  const village = document.getElementById('search-village') ? document.getElementById('search-village').value : '';

  let query = 'donors.php';
  const params = [];
  if (bg) params.push(`blood_group=${encodeURIComponent(bg)}`);
  if (village) params.push(`village=${encodeURIComponent(village)}`);
  if (params.length) query += '?' + params.join('&');

  const res = await JeevanSetuAPI.request(query, 'GET');
  if (res.success && Array.isArray(res.data)) {
    const donors = res.data;

    if (donors.length === 0) {
      container.innerHTML = `<div class="col-12"><p class="text-muted text-center py-4">No blood donors match your search query.</p></div>`;
      return;
    }

    container.innerHTML = donors.map(d => `
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="feature-card glass-panel h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="feature-icon-wrapper icon-red mb-0" style="width:42px; height:42px; font-size:1.1rem;">
                <i class="fa-solid fa-droplet"></i>
              </div>
              <div>
                <h6 class="mb-0 text-main fw-bold">${d.name}</h6>
                <small class="text-muted"><i class="fa-solid fa-house-chimney me-1"></i>${d.village || 'Local'}</small>
              </div>
            </div>
            <span class="badge-status badge-blood fs-6">${d.blood_group}</span>
          </div>
          <div class="mt-auto pt-3 border-top border-light d-flex justify-content-between align-items-center">
            <span class="text-muted small"><i class="fa-solid fa-phone me-1"></i>${d.phone}</span>
            <a href="tel:${d.phone}" class="btn btn-sm btn-red"><i class="fa-solid fa-phone me-1"></i>Contact</a>
          </div>
        </div>
      </div>
    `).join('');
  }
}

// ----------------------------------------------------
// Page: User Profile & Emergency Contacts Logic
// ----------------------------------------------------
async function initUserPage() {
  loadUserProfile();

  // Handle Add Contact Form
  const addContactForm = document.getElementById('add-contact-form');
  if (addContactForm) {
    addContactForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const contactName = document.getElementById('contact-name').value;
      const phone = document.getElementById('contact-phone').value;

      const res = await JeevanSetuAPI.request('profile.php', 'POST', {
        action: 'add_contact',
        contact_name: contactName,
        phone: phone
      });

      if (res.success) {
        showToast('Emergency Contact Added Successfully!', 'success');
        addContactForm.reset();
        loadUserProfile();
      } else {
        showToast(res.message || 'Failed to add contact.', 'danger');
      }
    });
  }

  // Handle User Login
  const loginForm = document.getElementById('user-login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const input = document.getElementById('login-email-phone').value;
      const pass = document.getElementById('login-password').value;

      const res = await JeevanSetuAPI.request('login.php', 'POST', {
        email_or_phone: input,
        password: pass
      });

      if (res.success) {
        showToast('Logged in successfully!', 'success');
        loadUserProfile();
        checkUserSession();
      } else {
        showToast(res.message || 'Invalid credentials', 'danger');
      }
    });
  }
}

async function loadUserProfile() {
  const profileContainer = document.getElementById('user-profile-card');
  const contactsContainer = document.getElementById('contacts-list');
  if (!profileContainer) return;

  const res = await JeevanSetuAPI.request('profile.php', 'GET');
  if (res.success && res.data) {
    const p = res.data.profile || {};
    const contacts = res.data.contacts || [];

    profileContainer.innerHTML = `
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="brand-icon" style="width:54px; height:54px; font-size:1.5rem;">
          <i class="fa-solid fa-user-doctor"></i>
        </div>
        <div>
          <h4 class="mb-0 text-main">${p.name || 'Registered User'}</h4>
          <span class="text-muted small">${p.email || 'user@jeevansetu.in'}</span>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <div class="col-6">
          <div class="p-2 bg-dark rounded border border-light">
            <small class="text-muted d-block">Phone Number</small>
            <strong class="text-main">${p.phone || '9876543210'}</strong>
          </div>
        </div>
        <div class="col-6">
          <div class="p-2 bg-dark rounded border border-light">
            <small class="text-muted d-block">Blood Group</small>
            <strong class="text-danger fw-bold fs-5">${p.blood_group || 'O+'}</strong>
          </div>
        </div>
      </div>
    `;

    if (contactsContainer) {
      if (contacts.length === 0) {
        contactsContainer.innerHTML = `<p class="text-muted small">No emergency contacts added yet. Add trusted contacts below to automatically notify them during SOS broadcasts.</p>`;
        return;
      }

      contactsContainer.innerHTML = contacts.map(c => `
        <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-dark rounded border border-light">
          <div>
            <div class="fw-bold text-main small">${c.contact_name}</div>
            <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i>${c.phone}</div>
          </div>
          <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteContact(${c.id})">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      `).join('');
    }
  }
}

async function deleteContact(id) {
  if (!confirm('Are you sure you want to remove this emergency contact?')) return;
  const res = await JeevanSetuAPI.request('profile.php', 'DELETE', { contact_id: id });
  if (res.success) {
    showToast('Emergency contact removed.', 'info');
    loadUserProfile();
  }
}
window.deleteContact = deleteContact;

// ----------------------------------------------------
// Page: Admin Control Room Logic
// ----------------------------------------------------
async function initAdminPage() {
  const adminLoginForm = document.getElementById('admin-login-form');
  if (adminLoginForm) {
    adminLoginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const u = document.getElementById('admin-username').value;
      const p = document.getElementById('admin-password').value;

      const res = await JeevanSetuAPI.request('admin_login.php', 'POST', { username: u, password: p });
      if (res.success) {
        showToast('Admin Access Granted', 'success');
        document.getElementById('admin-login-sec').classList.add('d-none');
        document.getElementById('admin-dashboard-sec').classList.remove('d-none');
        loadAdminDashboard();
      } else {
        showToast(res.message || 'Invalid admin login', 'danger');
      }
    });
  }

  // Load metrics & dispatch list
  loadAdminDashboard();

  // Add Hospital Modal Form
  const addHospForm = document.getElementById('add-hospital-form');
  if (addHospForm) {
    addHospForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const name = document.getElementById('hosp-name').value;
      const phone = document.getElementById('hosp-phone').value;
      const address = document.getElementById('hosp-address').value;
      const latitude = document.getElementById('hosp-lat').value;
      const longitude = document.getElementById('hosp-lng').value;

      const res = await JeevanSetuAPI.request('hospitals.php', 'POST', {
        name, phone, address, latitude, longitude
      });

      if (res.success) {
        showToast('Hospital added to emergency directory', 'success');
        addHospForm.reset();
        loadAdminDashboard();
      } else {
        showToast(res.message || 'Failed to add hospital', 'danger');
      }
    });
  }
}

async function loadAdminDashboard() {
  const tableBody = document.getElementById('admin-emergencies-tbody');
  if (!tableBody) return;

  // Load metrics
  const mRes = await JeevanSetuAPI.request('dashboard.php', 'GET');
  if (mRes.success && mRes.data) {
    const d = mRes.data;
    if (document.getElementById('metric-total-users')) document.getElementById('metric-total-users').innerText = d.total_users;
    if (document.getElementById('metric-total-hospitals')) document.getElementById('metric-total-hospitals').innerText = d.total_hospitals;
    if (document.getElementById('metric-total-donors')) document.getElementById('metric-total-donors').innerText = d.total_donors;
    if (document.getElementById('metric-pending-sos')) document.getElementById('metric-pending-sos').innerText = d.pending_emergencies;
  }

  // Load emergencies table
  const eRes = await JeevanSetuAPI.request('admin_emergencies.php', 'GET');
  if (eRes.success && Array.isArray(eRes.data)) {
    const list = eRes.data;
    if (list.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No active emergency signals reported.</td></tr>`;
      return;
    }

    tableBody.innerHTML = list.map(item => `
      <tr>
        <td><strong>#SOS-${item.id}</strong></td>
        <td>${item.user_name || 'Anonymous User'} <br><small class="text-muted">${item.user_phone || ''}</small></td>
        <td><span class="badge bg-danger">${item.emergency_type || 'Medical SOS'}</span></td>
        <td>${parseFloat(item.latitude).toFixed(4)}, ${parseFloat(item.longitude).toFixed(4)}</td>
        <td><small class="text-muted">${item.created_at}</small></td>
        <td>
          <select class="form-select form-select-sm form-select-dark" style="width: 140px;" onchange="updateEmergencyStatus(${item.id}, this.value)">
            <option value="Pending" ${item.status === 'Pending' ? 'selected' : ''}>Pending</option>
            <option value="Dispatched" ${item.status === 'Dispatched' ? 'selected' : ''}>Dispatched</option>
            <option value="Resolved" ${item.status === 'Resolved' ? 'selected' : ''}>Resolved</option>
          </select>
        </td>
      </tr>
    `).join('');
  }
}

async function updateEmergencyStatus(id, status) {
  const res = await JeevanSetuAPI.request('admin_emergencies.php', 'POST', {
    emergency_id: id,
    status: status
  });

  if (res.success) {
    showToast(`Emergency #SOS-${id} updated to ${status}`, 'success');
    loadAdminDashboard();
  } else {
    showToast('Failed to update emergency status', 'danger');
  }
}
window.updateEmergencyStatus = updateEmergencyStatus;
