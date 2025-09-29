import { Map, TileLayer, Marker, Icon } from 'leaflet';

function getStatusColor(status) {
  const colors = {
    pending: '#f59e0b',
    preparing: '#3b82f6',
    out_for_delivery: '#10b981',
    delivered: '#22c55e',
    cancelled: '#ef4444'
  };
  return colors[status] || '#3b82f6';
}

function parseOrders() {
  const el = document.getElementById('orders-data');
  if (!el) return [];
  try {
    const json = el.textContent || '[]';
    return JSON.parse(json);
  } catch (e) {
    console.error('Failed to parse orders JSON', e);
    return [];
  }
}

// Store markers globally for filtering
window.orderMarkers = [];

function initOrdersMap() {
  const container = document.getElementById('orders-map');
  if (!container) return;

  if (window.ordersMap) {
    window.ordersMap.remove();
    window.ordersMap = null;
  }
  
  // Clear existing markers
  window.orderMarkers = [];

  const orders = parseOrders();
  const coords = orders
    .filter(o => o && o.latitude && o.longitude)
    .map(o => [parseFloat(o.latitude), parseFloat(o.longitude)]);

  const defaultCenter = coords.length ? coords[0] : [14.5995, 120.9842];
  const map = (window.ordersMap = new Map('orders-map').setView(defaultCenter, coords.length ? 13 : 5));

  new TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  const urlTemplate = container.getAttribute('data-order-show-url-template') || '';

  orders.forEach(o => {
    if (!o || !o.latitude || !o.longitude) return;
    const lat = parseFloat(o.latitude);
    const lng = parseFloat(o.longitude);
    const status = o.delivery_status || o.status || 'pending';
    const color = getStatusColor(status);

    // Create a pin icon
    const pinIcon = new Icon({
      iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34],
      shadowSize: [41, 41]
    });
    
    const marker = new Marker([lat, lng], {
      icon: pinIcon,
      title: o.order_number || `Order #${o.id}`
    }).addTo(map);
    
    // Store marker with its status for filtering
    window.orderMarkers.push({
      marker,
      status,
      order: o
    });

    const address = o.exact_address || '';
    const statusLabel = status.replace(/_/g, ' ').toUpperCase();

    const popupHtml =
      '<div style="padding:8px;">' +
      '<strong>Order: ' + (o.order_number || o.id) + '</strong><br>' +
      '<span style="color:' + color + ';">' + statusLabel + '</span><br>' +
      '<small>' + address + '</small><br>' +
      '<button class="px-3 py-1 mt-2 text-sm rounded border" data-order-id="' +
      o.id +
      '">View Details</button>' +
      '</div>';

    marker.bindPopup(popupHtml);
    marker.on('popupopen', e => {
      const btn = e.popup.getElement().querySelector('button[data-order-id]');
      if (btn) {
        btn.addEventListener('click', () => {
          const id = o.id;
          // Prefer hard navigation to the order show page for a smooth details view
          if (urlTemplate && id != null) {
            const href = urlTemplate.replace('ORDER_ID', String(id));
            window.location.href = href;
            return;
          }
          // Fallback: emit Livewire event (if any listener is attached)
          if (window.Livewire) {
            try {
              Livewire.dispatch ? Livewire.dispatch('selectOrder', { id }) : Livewire.emit('selectOrder', id);
            } catch (_) {}
          }
        });
      }
    });
  });
}

function initSidebarActions() {
  // Handle existing sidebar buttons
  document.querySelectorAll('.view-details-btn').forEach(el => {
    el.addEventListener('click', (ev) => {
      // anchors will navigate; no special handling needed
    });
  });
  
  // Map sidebar toggle
  const sidebarToggle = document.getElementById('map-sidebar-toggle');
  const sidebar = document.getElementById('map-sidebar');
  const sidebarClose = document.getElementById('map-sidebar-close');
  
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.add('open');
    });
  }
  if (sidebarClose && sidebar) {
    sidebarClose.addEventListener('click', () => {
      sidebar.classList.remove('open');
    });
  }
  
  // Optional: Fit all markers button (present only if element exists)
  const fitButtonEl = document.getElementById('fit-all-markers');
  if (fitButtonEl) {
    fitButtonEl.addEventListener('click', () => {
      if (window.ordersMap && window.orderMarkers && window.orderMarkers.length) {
        const bounds = L.latLngBounds(window.orderMarkers.map(item => item.marker.getLatLng()));
        window.ordersMap.fitBounds(bounds, { padding: [50, 50] });
      }
    });
  }
  
  // Helper to get checked statuses; fallback to all statuses when filters are absent
  function getCheckedStatuses() {
    const checkboxes = document.querySelectorAll('.status-filter:checked');
    if (!checkboxes || checkboxes.length === 0) {
      return ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
    }
    return Array.from(checkboxes).map(cb => cb.value);
  }
  
  // Hook changes if filters exist
  document.querySelectorAll('.status-filter').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
      applyFilters(getCheckedStatuses());
    });
  });
}

function applyFilters(checkedStatuses = ['pending','preparing','out_for_delivery','delivered','cancelled']) {
  if (!window.orderMarkers || !window.ordersMap) return;
  window.orderMarkers.forEach(item => {
    const shouldShow = checkedStatuses.includes(item.status);
    if (shouldShow) {
      if (!window.ordersMap.hasLayer(item.marker)) {
        item.marker.addTo(window.ordersMap);
      }
    } else {
      if (window.ordersMap.hasLayer(item.marker)) {
        window.ordersMap.removeLayer(item.marker);
      }
    }
  });
}

function fitAllMarkers() {
  if (!window.ordersMap || !window.orderMarkers || window.orderMarkers.length === 0) return;
  const checkedStatuses = (function(){
    const boxes = document.querySelectorAll('.status-filter:checked');
    if (!boxes || boxes.length === 0) {
      return ['pending','preparing','out_for_delivery','delivered','cancelled'];
    }
    return Array.from(boxes).map(x => x.value);
  })();
  const visibleMarkers = window.orderMarkers.filter(item => checkedStatuses.includes(item.status)).map(item => item.marker);
  if (visibleMarkers.length === 0) return;
  const bounds = L.latLngBounds(visibleMarkers.map(m => m.getLatLng()));
  window.ordersMap.fitBounds(bounds, { padding: [50, 50] });
}

function boot() {
  initOrdersMap();
  initSidebarActions();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot);
} else {
  boot();
}