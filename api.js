/**
 * JEEVANSETU - API Client Module
 * Communicates with Core PHP API endpoints with automatic fallback to mock data when running standalone.
 */

const API_BASE_URL = window.location.origin + '/api';

// Mock Data Storage for Standalone/Offline Preview
const MOCK_STORAGE_KEY = 'jeevansetu_mock_db';

function getMockDB() {
  const existing = localStorage.getItem(MOCK_STORAGE_KEY);
  if (existing) {
    return JSON.parse(existing);
  }

  const initialDB = {
    user: {
      id: 1,
      name: "Rajesh Kumar",
      email: "rajesh.kumar@example.com",
      phone: "9876543210",
      blood_group: "O+"
    },
    contacts: [
      { id: 101, contact_name: "Sunita Kumar (Wife)", phone: "9876500001" },
      { id: 102, contact_name: "Dr. Vikram Singh (Family Doctor)", phone: "9876500002" }
    ],
    hospitals: [
      { id: 1, name: "Apex Emergency Rural Hospital", phone: "+91 9811223344", address: "National Highway 44, Rampur Village", latitude: 28.6139, longitude: 77.2090 },
      { id: 2, name: "Sanjeevani Trauma & Care Center", phone: "+91 9822334455", address: "Near Railway Station, Chandanpur", latitude: 28.6250, longitude: 77.2180 },
      { id: 3, name: "District Primary Health Center (PHC)", phone: "+91 9833445566", address: "Main Bazaar, Sundarnagar", latitude: 28.6010, longitude: 77.1980 },
      { id: 4, name: "Red Cross Life Line Emergency Unit", phone: "+91 9844556677", address: "Sector 4, Greenfield Enclave", latitude: 28.6320, longitude: 77.2310 }
    ],
    donors: [
      { id: 1, name: "Ramesh Sharma", blood_group: "O+", village: "Rampur", phone: "9988776655" },
      { id: 2, name: "Anita Verma", blood_group: "A+", village: "Sundarnagar", phone: "9877665544" },
      { id: 3, name: "Vikram Patel", blood_group: "B+", village: "Rampur", phone: "9766554433" },
      { id: 4, name: "Suresh Yadav", blood_group: "O-", village: "Chandanpur", phone: "9655443322" },
      { id: 5, name: "Pooja Singh", blood_group: "AB+", village: "Sundarnagar", phone: "9544332211" }
    ],
    emergencies: [
      { id: 501, user_id: 1, user_name: "Rajesh Kumar", user_phone: "9876543210", emergency_type: "Medical Emergency", latitude: 28.6139, longitude: 77.2090, created_at: "2026-08-15 21:30:00", status: "Pending" },
      { id: 500, user_id: 2, user_name: "Amit Patel", user_phone: "9123456789", emergency_type: "Accident / Trauma", latitude: 28.6210, longitude: 77.2150, created_at: "2026-08-15 19:45:00", status: "Dispatched" },
      { id: 499, user_id: 3, user_name: "Priya Sharma", user_phone: "9812345678", emergency_type: "Urgent Blood Requirement", latitude: 28.6050, longitude: 77.2010, created_at: "2026-08-14 14:15:00", status: "Resolved" }
    ],
    admin: null
  };

  localStorage.setItem(MOCK_STORAGE_KEY, JSON.stringify(initialDB));
  return initialDB;
}

function saveMockDB(db) {
  localStorage.setItem(MOCK_STORAGE_KEY, JSON.stringify(db));
}

class JeevanSetuAPI {
  // Generic fetch wrapper with fallback
  static async request(endpoint, method = 'GET', data = null) {
    const url = `${API_BASE_URL}/${endpoint}`;
    const options = {
      method: method,
      headers: {
        'Content-Type': 'application/json'
      }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
      options.body = JSON.stringify(data);
    }

    try {
      const response = await fetch(url, options);
      if (!response.ok && response.status === 404) {
        throw new Error('Endpoint not found (Running standalone mode)');
      }
      const json = await response.json();
      return json;
    } catch (err) {
      console.warn(`[JeevanSetu API] Native fetch to ${endpoint} failed or offline. Switching to Mock Fallback.`, err);
      return JeevanSetuAPI.handleMockRequest(endpoint, method, data);
    }
  }

  // Fallback Mock Logic
  static handleMockRequest(endpoint, method, data) {
    const db = getMockDB();
    
    // Auth Check / Login
    if (endpoint === 'login.php' && method === 'POST') {
      const { email_or_phone, password } = data;
      if (email_or_phone && password) {
        db.user.email = email_or_phone.includes('@') ? email_or_phone : db.user.email;
        saveMockDB(db);
        return { success: true, message: 'Login successful (Mock Mode)', data: { user: db.user } };
      }
      return { success: false, message: 'Invalid credentials' };
    }

    // Register
    if (endpoint === 'register.php' && method === 'POST') {
      const newUser = {
        id: Date.now(),
        name: data.name,
        email: data.email,
        phone: data.phone,
        blood_group: data.blood_group
      };
      db.user = newUser;
      saveMockDB(db);
      return { success: true, message: 'Registration successful (Mock Mode)', data: { user_id: newUser.id } };
    }

    // SOS Emergency Dispatch
    if (endpoint === 'sos.php') {
      if (method === 'POST') {
        const newEmergency = {
          id: Math.floor(100 + Math.random() * 900),
          user_id: db.user ? db.user.id : 1,
          user_name: db.user ? db.user.name : "Anonymous Emergency",
          user_phone: db.user ? db.user.phone : "Unknown",
          emergency_type: data.emergency_type || "Medical Emergency",
          latitude: parseFloat(data.latitude) || 28.6139,
          longitude: parseFloat(data.longitude) || 77.2090,
          created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
          status: "Pending"
        };
        db.emergencies.unshift(newEmergency);
        saveMockDB(db);
        return { success: true, message: 'Emergency SOS Broadcasted Successfully!', data: newEmergency };
      } else {
        // GET User History
        return { success: true, message: 'User emergencies fetched', data: db.emergencies };
      }
    }

    // Hospitals
    if (endpoint.startsWith('hospitals.php')) {
      if (method === 'GET') {
        return { success: true, message: 'Hospitals loaded', data: db.hospitals };
      } else if (method === 'POST') {
        const newHospital = {
          id: db.hospitals.length + 1,
          name: data.name,
          phone: data.phone,
          address: data.address,
          latitude: parseFloat(data.latitude),
          longitude: parseFloat(data.longitude)
        };
        db.hospitals.push(newHospital);
        saveMockDB(db);
        return { success: true, message: 'Hospital added successfully', data: newHospital };
      }
    }

    // Blood Donors
    if (endpoint.startsWith('donors.php')) {
      if (method === 'GET') {
        let results = db.donors;
        const urlParams = new URLSearchParams(endpoint.split('?')[1] || '');
        const bg = urlParams.get('blood_group');
        const village = urlParams.get('village');

        if (bg) results = results.filter(d => d.blood_group.toLowerCase() === bg.toLowerCase());
        if (village) results = results.filter(d => d.village.toLowerCase().includes(village.toLowerCase()));

        return { success: true, message: 'Blood donors fetched', data: results };
      } else if (method === 'POST') {
        const newDonor = {
          id: db.donors.length + 1,
          name: data.name,
          blood_group: data.blood_group,
          village: data.village,
          phone: data.phone
        };
        db.donors.push(newDonor);
        saveMockDB(db);
        return { success: true, message: 'Donor registered successfully!', data: newDonor };
      }
    }

    // Profile & Emergency Contacts
    if (endpoint === 'profile.php') {
      if (method === 'GET') {
        return { success: true, message: 'Profile data', data: { profile: db.user, contacts: db.contacts } };
      } else if (method === 'POST') {
        if (data.action === 'add_contact') {
          const newContact = { id: Date.now(), contact_name: data.contact_name, phone: data.phone };
          db.contacts.push(newContact);
          saveMockDB(db);
          return { success: true, message: 'Emergency contact added', data: newContact };
        } else {
          db.user.name = data.name || db.user.name;
          db.user.phone = data.phone || db.user.phone;
          db.user.blood_group = data.blood_group || db.user.blood_group;
          saveMockDB(db);
          return { success: true, message: 'Profile updated' };
        }
      } else if (method === 'DELETE') {
        const cid = data.contact_id;
        db.contacts = db.contacts.filter(c => c.id !== cid);
        saveMockDB(db);
        return { success: true, message: 'Emergency contact removed' };
      }
    }

    // Admin Dashboard
    if (endpoint === 'dashboard.php') {
      const pendingCount = db.emergencies.filter(e => e.status === 'Pending').length;
      const resolvedCount = db.emergencies.filter(e => e.status === 'Resolved').length;
      return {
        success: true,
        data: {
          total_users: 142,
          total_hospitals: db.hospitals.length,
          total_donors: db.donors.length,
          today_emergencies: db.emergencies.length,
          pending_emergencies: pendingCount,
          resolved_emergencies: resolvedCount
        }
      };
    }

    // Admin Emergencies
    if (endpoint === 'admin_emergencies.php') {
      if (method === 'GET') {
        return { success: true, data: db.emergencies };
      } else if (method === 'POST' || method === 'PUT') {
        const { emergency_id, status } = data;
        const target = db.emergencies.find(e => e.id === Number(emergency_id));
        if (target) {
          target.status = status;
          saveMockDB(db);
          return { success: true, message: `Emergency status updated to ${status}` };
        }
        return { success: false, message: 'Emergency record not found' };
      }
    }

    // Admin Login
    if (endpoint === 'admin_login.php') {
      if (data.username === 'admin' && data.password === 'admin123') {
        db.admin = { id: 1, username: 'admin' };
        saveMockDB(db);
        return { success: true, message: 'Admin authenticated', data: { admin: db.admin } };
      }
      return { success: false, message: 'Invalid admin credentials (Use: admin / admin123)' };
    }

    return { success: true, message: 'Mock response OK', data: [] };
  }
}

window.JeevanSetuAPI = JeevanSetuAPI;
