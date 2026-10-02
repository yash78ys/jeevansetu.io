/**
 * JEEVANSETU - Leaflet Map Engine & GPS Geolocation Helper
 */

class JeevanSetuMap {
  constructor(elementId, initialLat = 28.6139, initialLng = 77.2090, zoomLevel = 13) {
    this.elementId = elementId;
    this.lat = initialLat;
    this.lng = initialLng;
    this.zoom = zoomLevel;
    this.map = null;
    this.userMarker = null;
    this.markers = [];
  }

  init() {
    const container = document.getElementById(this.elementId);
    if (!container || typeof L === 'undefined') {
      console.warn(`[JeevanSetu Map] Leaflet library or element #${this.elementId} not present.`);
      return;
    }

    this.map = L.map(this.elementId, {
      zoomControl: true,
      attributionControl: false
    }).setView([this.lat, this.lng], this.zoom);

    // High quality OpenStreetMap tiles with custom styling
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors'
    }).addTo(this.map);

    this.addUserMarker(this.lat, this.lng, "Your Detected Location");
    return this.map;
  }

  // Set marker for User's current location
  addUserMarker(lat, lng, popupText = "Your Location", isDraggable = false, onDragCallback = null) {
    if (this.userMarker) {
      this.map.removeLayer(this.userMarker);
    }

    const userIcon = L.divIcon({
      className: 'custom-user-pin',
      html: `
        <div style="
          width: 24px;
          height: 24px;
          background: #06b6d4;
          border: 3px solid #ffffff;
          border-radius: 50%;
          box-shadow: 0 0 15px rgba(6, 182, 212, 0.8);
          animation: pulse 1.8s infinite;
        "></div>
      `,
      iconSize: [24, 24],
      iconAnchor: [12, 12]
    });

    this.userMarker = L.marker([lat, lng], {
      icon: userIcon,
      draggable: isDraggable
    }).addTo(this.map);

    this.userMarker.bindPopup(`
      <div class="popup-title"><i class="fa-solid fa-location-crosshairs text-info me-1"></i> ${popupText}</div>
      <div class="popup-meta">Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}</div>
    `).openPopup();

    if (isDraggable && onDragCallback) {
      this.userMarker.on('dragend', (e) => {
        const coords = e.target.getLatLng();
        this.lat = coords.lat;
        this.lng = coords.lng;
        onDragCallback(coords.lat, coords.lng);
      });
    }

    this.map.panTo([lat, lng]);
  }

  // Add hospital pin to map
  addHospitalMarker(hospital) {
    if (!this.map) return;

    const hospitalIcon = L.divIcon({
      className: 'custom-hospital-pin',
      html: `
        <div style="
          width: 32px;
          height: 32px;
          background: #10b981;
          border: 2px solid #ffffff;
          border-radius: 8px;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-size: 14px;
          box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        ">
          <i class="fa-solid fa-hospital"></i>
        </div>
      `,
      iconSize: [32, 32],
      iconAnchor: [16, 16]
    });

    const marker = L.marker([hospital.latitude, hospital.longitude], { icon: hospitalIcon }).addTo(this.map);
    
    let distText = '';
    if (this.lat && this.lng) {
      const d = JeevanSetuMap.calculateDistance(this.lat, this.lng, hospital.latitude, hospital.longitude);
      distText = `<span class="badge bg-success ms-1">${d.toFixed(1)} km away</span>`;
    }

    marker.bindPopup(`
      <div class="popup-title">${hospital.name} ${distText}</div>
      <div class="popup-meta"><i class="fa-solid fa-location-dot me-1 text-danger"></i> ${hospital.address}</div>
      <div class="popup-meta mt-1"><i class="fa-solid fa-phone me-1 text-success"></i> ${hospital.phone}</div>
      <div class="mt-2">
        <a href="tel:${hospital.phone}" class="btn btn-sm btn-success py-1 px-2 me-1" style="font-size:0.75rem;"><i class="fa-solid fa-phone me-1"></i>Call</a>
        <a href="https://maps.google.com/?q=${hospital.latitude},${hospital.longitude}" target="_blank" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:0.75rem;"><i class="fa-solid fa-directions me-1"></i>Directions</a>
      </div>
    `);

    this.markers.push(marker);
  }

  // Add Emergency Pin to map
  addEmergencyMarker(emergency) {
    if (!this.map) return;

    const sosIcon = L.divIcon({
      className: 'custom-sos-pin',
      html: `
        <div style="
          width: 36px;
          height: 36px;
          background: #ef4444;
          border: 3px solid #ffffff;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-size: 16px;
          box-shadow: 0 0 20px rgba(239, 68, 68, 0.9);
          animation: pulse 1.5s infinite;
        ">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
      `,
      iconSize: [36, 36],
      iconAnchor: [18, 18]
    });

    const marker = L.marker([emergency.latitude, emergency.longitude], { icon: sosIcon }).addTo(this.map);
    
    marker.bindPopup(`
      <div class="popup-title text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> ${emergency.emergency_type || 'SOS Alert'}</div>
      <div class="popup-meta"><strong>User:</strong> ${emergency.user_name || 'Emergency Beacon'}</div>
      <div class="popup-meta"><strong>Phone:</strong> ${emergency.user_phone || 'N/A'}</div>
      <div class="popup-meta"><strong>Status:</strong> <span class="badge bg-warning text-dark">${emergency.status}</span></div>
      <div class="popup-meta mt-1"><small>${emergency.created_at}</small></div>
    `);

    this.markers.push(marker);
  }

  // Auto detect user GPS position
  detectLocation(callback) {
    if (!navigator.geolocation) {
      console.warn('[JeevanSetu Map] Browser Geolocation not supported. Using default.');
      if (callback) callback(this.lat, this.lng);
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        this.lat = pos.coords.latitude;
        this.lng = pos.coords.longitude;
        if (this.map) {
          this.addUserMarker(this.lat, this.lng, "GPS Location Verified", true, callback);
        }
        if (callback) callback(this.lat, this.lng);
      },
      (err) => {
        console.warn('[JeevanSetu Map] Geolocation permission denied or unavailable. Using standard center.', err);
        if (callback) callback(this.lat, this.lng);
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  }

  // Haversine distance formula in kilometers
  static calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of Earth in KM
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }
}

window.JeevanSetuMap = JeevanSetuMap;
