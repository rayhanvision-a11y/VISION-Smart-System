<x-app-layout>
    {{-- 1. Leaflet CSS & JS Loaded at the Top from High-Availability CDNs --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    <style>
        /* Explicit Leaflet Canvas & Panes */
        #live-map-canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background: #0f172a;
        }

        .leaflet-popup-content-wrapper {
            padding: 0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dark .leaflet-popup-content-wrapper {
            background: #1e293b;
            border-color: #334155;
        }
        .leaflet-popup-content {
            margin: 0;
            line-height: 1.4;
        }
        .leaflet-popup-tip-container {
            display: none;
        }

        /* App-identical Circular Halo Marker matching Flutter screenshot */
        .app-marker-container {
            position: relative;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .app-marker-container:hover {
            transform: scale(1.22);
            z-index: 1000 !important;
        }
        .app-marker-halo {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            transition: all 0.3s;
        }
        .halo-online {
            background-color: rgba(16, 185, 129, 0.32);
            box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.18);
            animation: halo-breathe 2.5s infinite ease-in-out;
        }
        .halo-idle {
            background-color: rgba(245, 158, 11, 0.28);
        }
        .halo-offline {
            background-color: rgba(100, 116, 139, 0.22);
        }

        @keyframes halo-breathe {
            0%, 100% { transform: scale(0.95); opacity: 0.85; }
            50% { transform: scale(1.18); opacity: 0.35; }
        }

        .app-marker-inner {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2.5px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
        }
        .app-marker-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .inner-online { background-color: #10b981; }
        .inner-idle { background-color: #f59e0b; }
        .inner-offline { background-color: #64748b; }

        .app-marker-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 11px;
            height: 11px;
            border-radius: 50%;
            border: 2px solid #ffffff;
        }

        /* Glassmorphism floating panels */
        .glass-panel {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, 0.96);
            border-color: rgba(51, 65, 85, 0.85);
        }

        /* Explicit Floating Overlays to override Leaflet z-indexes (Pane=400, Marker=600, Popup=700) */
        .map-floating-top {
            position: absolute;
            top: 16px;
            left: 16px;
            right: 16px;
            z-index: 1000 !important;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            pointer-events: none;
        }

        .map-floating-legend {
            position: absolute;
            bottom: 20px;
            left: 16px;
            z-index: 1000 !important;
            pointer-events: auto;
        }

        .map-drawer-panel {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 360px;
            max-width: 90vw;
            z-index: 1200 !important;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-right: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            display: flex;
            flex-direction: column;
        }
        .dark .map-drawer-panel {
            background: rgba(15, 23, 42, 0.98);
            border-color: rgba(51, 65, 85, 0.85);
        }

        .map-detail-card {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 420px;
            max-width: calc(100vw - 32px);
            max-height: 85vh;
            z-index: 1300 !important;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1.5rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            padding: 1.25rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .dark .map-detail-card {
            background: rgba(15, 23, 42, 0.98);
            border-color: rgba(51, 65, 85, 0.85);
        }
        @media (max-width: 640px) {
            .map-detail-card {
                left: 12px;
                right: 12px;
                bottom: 12px;
                width: auto;
            }
        }
    </style>

    {{-- 2. Alpine Component Function Defined on window BEFORE HTML uses it --}}
    <script>
        window.liveStaffMap = function() {
            return {
                map: null,
                markers: {},
                technicians: [],
                searchQuery: '',
                roleFilter: '',
                roleFilterLabel: 'All Staff',
                statusFilter: '',
                showDirectory: false,
                selectedTechnician: null,
                loading: false,
                locating: false,
                loadingTrail: false,
                showingTrail: false,
                trailPolyline: null,
                tileLayers: {},
                currentMapStyle: 'osm',
                autoRefresh: true,
                refreshInterval: null,
                initialFitDone: false,

                get activeLocationsCount() {
                    return this.technicians.filter(t => t.has_location).length;
                },

                init() {
                    this.pollLeaflet();
                },

                pollLeaflet() {
                    if (typeof L !== 'undefined' && typeof L.map === 'function') {
                        this.initMap();
                        this.fetchLocations();

                        // 15 seconds auto-poll
                        this.refreshInterval = setInterval(() => {
                            if (this.autoRefresh) {
                                this.fetchLocations(false);
                            }
                        }, 15000);
                    } else {
                        setTimeout(() => this.pollLeaflet(), 100);
                    }
                },

                initMap() {
                    const canvas = document.getElementById('live-map-canvas');
                    if (!canvas || this.map) return;

                    // Pabna City Center (Matching Mobile App Default)
                    const defaultLat = 24.0064;
                    const defaultLng = 89.2372;

                    this.map = L.map('live-map-canvas', {
                        zoomControl: false,
                        attributionControl: false
                    }).setView([defaultLat, defaultLng], 13);

                    L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                    // 1. OpenStreetMap Standard (Exact same layer as Mobile App with Bengali road labels)
                    this.tileLayers['osm'] = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    });

                    // 2. Esri World Street Map (100% Free, Crisp alternative street view)
                    this.tileLayers['street'] = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19
                    });

                    // 3. Esri World Imagery (Satellite)
                    this.tileLayers['satellite'] = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19
                    });

                    // Default to OpenStreetMap (exact match to Mobile App)
                    this.tileLayers['osm'].addTo(this.map);

                    // Force Leaflet to invalidate size so canvas renders instantly
                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 200);

                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 800);

                    window.addEventListener('resize', () => {
                        if (this.map) this.map.invalidateSize();
                    });
                },

                setMapStyle(style) {
                    if (this.currentMapStyle === style) return;
                    if (this.map && this.tileLayers[this.currentMapStyle]) {
                        this.map.removeLayer(this.tileLayers[this.currentMapStyle]);
                    }
                    if (this.map && this.tileLayers[style]) {
                        this.tileLayers[style].addTo(this.map);
                        this.currentMapStyle = style;
                    }
                },

                toggleAutoRefresh() {
                    this.autoRefresh = !this.autoRefresh;
                },

                async fetchLocations(manual = false) {
                    if (manual) this.loading = true;

                    try {
                        const url = new URL("{{ route('map.data') }}", window.location.origin);
                        if (this.roleFilter) url.searchParams.set('role', this.roleFilter);

                        const res = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!res.ok) throw new Error('Failed to load location data');
                        const json = await res.json();

                        this.technicians = json.technicians || [];
                        this.renderMarkers();

                        // Refresh selected technician data if currently open
                        if (this.selectedTechnician) {
                            const updated = this.technicians.find(t => t.user_id === this.selectedTechnician.user_id);
                            if (updated) {
                                this.selectedTechnician = updated;
                            }
                        }
                    } catch (err) {
                        console.error('Map update error:', err);
                    } finally {
                        this.loading = false;
                    }
                },

                get filteredTechnicians() {
                    return this.technicians.filter(t => {
                        const q = this.searchQuery.toLowerCase().trim();
                        const matchesSearch = !q ||
                            (t.name && t.name.toLowerCase().includes(q)) ||
                            (t.team && t.team.toLowerCase().includes(q)) ||
                            (t.phone && t.phone.toLowerCase().includes(q));

                        const matchesStatus = !this.statusFilter || t.status === this.statusFilter;

                        return matchesSearch && matchesStatus;
                    });
                },

                renderMarkers() {
                    if (!this.map) return;
                    const activeIds = new Set();
                    const bounds = [];

                    this.technicians.forEach(t => {
                        if (!t.has_location) return;

                        // Apply status filter if active
                        if (this.statusFilter && t.status !== this.statusFilter) {
                            if (this.markers[t.user_id]) {
                                this.map.removeLayer(this.markers[t.user_id]);
                                delete this.markers[t.user_id];
                            }
                            return;
                        }

                        activeIds.add(t.user_id);
                        bounds.push([t.latitude, t.longitude]);

                        const initial = (t.name || '?').trim().charAt(0).toUpperCase();
                        const hasRealAvatar = t.avatar_url && !t.avatar_url.includes('ui-avatars.com') && !t.avatar_url.includes('gravatar.com');

                        // Exact Halo + Circular Avatar Marker matching the Mobile App Screenshot!
                        const iconHtml = `
                            <div class="app-marker-container" id="marker-user-${t.user_id}" title="${t.name}">
                                <div class="app-marker-halo halo-${t.status}"></div>
                                <div class="app-marker-inner inner-${t.status}">
                                    ${hasRealAvatar
                                        ? `<img src="${t.avatar_url}" alt="${t.name}" onerror="this.remove()"><span>${initial}</span>`
                                        : `<span>${initial}</span>`}
                                </div>
                                <div class="app-marker-dot inner-${t.status}"></div>
                            </div>
                        `;

                        const customIcon = L.divIcon({
                            className: 'custom-leaflet-marker',
                            html: iconHtml,
                            iconSize: [48, 48],
                            iconAnchor: [24, 24],
                            popupAnchor: [0, -24]
                        });

                        if (this.markers[t.user_id]) {
                            this.markers[t.user_id].setLatLng([t.latitude, t.longitude]);
                            this.markers[t.user_id].setIcon(customIcon);
                        } else {
                            const marker = L.marker([t.latitude, t.longitude], { icon: customIcon }).addTo(this.map);
                            
                            marker.on('click', () => {
                                this.selectTechnician(t);
                            });

                            // Mobile-app like tooltip on hover
                            marker.bindTooltip(`
                                <div style="font-family: inherit; padding: 2px 4px;">
                                    <div style="font-weight: 700; color: #1e293b;">${t.name}</div>
                                    <div style="font-size: 11px; color: #64748b;">${t.role} • <span style="text-transform: uppercase; font-weight: 600;">${t.status}</span></div>
                                </div>
                            `, {
                                direction: 'top',
                                offset: [0, -24],
                                opacity: 0.95
                            });

                            this.markers[t.user_id] = marker;
                        }
                    });

                    // Remove inactive or filtered out markers
                    Object.keys(this.markers).forEach(id => {
                        if (!activeIds.has(parseInt(id))) {
                            this.map.removeLayer(this.markers[id]);
                            delete this.markers[id];
                        }
                    });

                    // Auto-fit bounds on initial load
                    if (bounds.length > 0 && !this.selectedTechnician && !this.initialFitDone) {
                        if (bounds.length === 1) {
                            this.map.setView(bounds[0], 14);
                        } else {
                            this.map.fitBounds(bounds, { padding: [80, 80], maxZoom: 15 });
                        }
                        this.initialFitDone = true;
                    }
                },

                selectTechnician(tech) {
                    this.selectedTechnician = tech;
                    this.clearHistoryTrail();

                    if (tech.has_location && this.map) {
                        this.map.flyTo([tech.latitude, tech.longitude], 15, {
                            animate: true,
                            duration: 1.0
                        });
                    }
                },

                zoomToTechnician(tech) {
                    if (tech && tech.has_location && this.map) {
                        this.map.flyTo([tech.latitude, tech.longitude], 17, {
                            animate: true,
                            duration: 1.2
                        });
                    }
                },

                async toggleHistoryTrail(userId) {
                    if (this.showingTrail) {
                        this.clearHistoryTrail();
                        return;
                    }

                    this.loadingTrail = true;
                    try {
                        const res = await fetch(`{{ url('/live-map/history') }}/${userId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const json = await res.json();
                        const points = (json.points || []).map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);

                        if (points.length < 2) {
                            alert('No extended GPS history points found for this technician in the last 12 hours.');
                            return;
                        }

                        this.clearHistoryTrail();

                        this.trailPolyline = L.polyline(points, {
                            color: '#10b981',
                            weight: 4,
                            opacity: 0.85,
                            dashArray: '8, 8',
                            lineJoin: 'round'
                        }).addTo(this.map);

                        this.map.fitBounds(this.trailPolyline.getBounds(), { padding: [60, 60] });
                        this.showingTrail = true;
                    } catch (err) {
                        console.error('Trail fetch error:', err);
                    } finally {
                        this.loadingTrail = false;
                    }
                },

                clearHistoryTrail() {
                    if (this.trailPolyline && this.map) {
                        this.map.removeLayer(this.trailPolyline);
                        this.trailPolyline = null;
                    }
                    this.showingTrail = false;
                },

                locateMyPosition() {
                    if (!navigator.geolocation) {
                        alert('Geolocation is not supported by your browser.');
                        return;
                    }
                    this.locating = true;
                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            const acc = Math.round(pos.coords.accuracy || 0);

                            if (this.map) {
                                this.map.flyTo([lat, lng], 16, { animate: true, duration: 1.2 });
                            }

                            try {
                                const res = await fetch("{{ route('map.update-my-location') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ lat: lat, lng: lng, accuracy: acc })
                                });
                                if (res.ok) {
                                    await this.fetchLocations(false);
                                }
                            } catch (e) {
                                console.error('Failed to sync location', e);
                            } finally {
                                this.locating = false;
                            }
                        },
                        (err) => {
                            console.warn('Geolocation error:', err.message);
                            this.locating = false;
                            alert('Location access denied or unavailable: ' + err.message);
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                }
            };
        };
    </script>

    {{-- 3. Main Fullscreen Map Container --}}
    <div class="relative w-full overflow-hidden bg-slate-900"
         style="height: calc(100vh - 64px); min-height: 550px; position: relative;"
         x-data="window.liveStaffMap()">

        {{-- BIG FULL SCREEN MAP CANVAS --}}
        <div id="live-map-canvas"></div>

        {{-- TOP FLOATING APP BAR (Clean, modern floating controls like app) --}}
        <div class="map-floating-top">
            
            {{-- Left: Title & Tracked Staff Badge --}}
            <div class="pointer-events-auto glass-panel px-4 py-2.5 rounded-2xl flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5 leading-none">
                        <span>{{ __('Live Map') }}</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    </h1>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1">
                        <span x-text="activeLocationsCount">0</span> {{ __('staff tracked') }}
                        <span class="text-slate-400 font-normal">· (<span x-text="technicians.length"></span> {{ __('total') }})</span>
                    </p>
                </div>
            </div>

            {{-- Right: Map Style, Filter pills, Staff Drawer button & Refresh --}}
            <div class="pointer-events-auto flex items-center gap-2">

                {{-- Map Style Switcher (OSM, Street, Satellite) --}}
                <div class="relative" x-data="{ styleOpen: false }">
                    <button @click="styleOpen = !styleOpen"
                            title="{{ __('Change Map Style') }}"
                            class="glass-panel px-3 py-2 rounded-2xl text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span x-show="currentMapStyle === 'osm'">🌍 OpenStreetMap</span>
                        <span x-show="currentMapStyle === 'street'">🗺️ Esri Streets</span>
                        <span x-show="currentMapStyle === 'satellite'">🛰️ Satellite</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="styleOpen" @click.away="styleOpen = false"
                         style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 155px; z-index: 1100;"
                         class="glass-panel rounded-2xl py-1 text-xs shadow-2xl">
                        <button @click="setMapStyle('osm'); styleOpen = false"
                                :class="currentMapStyle === 'osm' ? 'text-emerald-600 font-bold bg-slate-100 dark:bg-slate-800' : 'text-slate-700 dark:text-slate-200'"
                                class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 dark:hover:bg-slate-800 font-medium flex items-center gap-2">
                            <span>🌍</span> OpenStreetMap
                        </button>
                        <button @click="setMapStyle('street'); styleOpen = false"
                                :class="currentMapStyle === 'street' ? 'text-emerald-600 font-bold bg-slate-100 dark:bg-slate-800' : 'text-slate-700 dark:text-slate-200'"
                                class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 dark:hover:bg-slate-800 font-medium flex items-center gap-2">
                            <span>🗺️</span> Esri Streets
                        </button>
                        <button @click="setMapStyle('satellite'); styleOpen = false"
                                :class="currentMapStyle === 'satellite' ? 'text-emerald-600 font-bold bg-slate-100 dark:bg-slate-800' : 'text-slate-700 dark:text-slate-200'"
                                class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 dark:hover:bg-slate-800 font-medium flex items-center gap-2">
                            <span>🛰️</span> Satellite
                        </button>
                    </div>
                </div>
                
                {{-- Role Filter Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="glass-panel px-3.5 py-2 rounded-2xl text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span x-text="roleFilterLabel">All Staff</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" @click.away="open = false"
                         style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 176px; z-index: 1100;"
                         class="glass-panel rounded-2xl py-1 text-xs shadow-2xl">
                        <button @click="roleFilter = ''; roleFilterLabel = 'All Staff'; open = false; fetchLocations(true)"
                                class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 dark:hover:bg-slate-800 font-medium flex items-center gap-2 text-slate-700 dark:text-slate-200">
                            <span>👥</span> {{ __('All Staff') }}
                        </button>
                        <button @click="roleFilter = 'technician'; roleFilterLabel = 'Technicians'; open = false; fetchLocations(true)"
                                class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 dark:hover:bg-slate-800 font-medium flex items-center gap-2 text-slate-700 dark:text-slate-200">
                            <span>👷</span> {{ __('Technicians') }}
                        </button>
                        <button @click="roleFilter = 'noc'; roleFilterLabel = 'NOC'; open = false; fetchLocations(true)"
                                class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 dark:hover:bg-slate-800 font-medium flex items-center gap-2 text-slate-700 dark:text-slate-200">
                            <span>🔧</span> {{ __('NOC') }}
                        </button>
                        <button @click="roleFilter = 'supervisor'; roleFilterLabel = 'Supervisors'; open = false; fetchLocations(true)"
                                class="w-full text-left px-3.5 py-2 hover:bg-indigo-50 dark:hover:bg-slate-800 font-medium flex items-center gap-2 text-slate-700 dark:text-slate-200">
                            <span>👀</span> {{ __('Supervisors') }}
                        </button>
                    </div>
                </div>

                {{-- Staff Directory Drawer Button --}}
                <button @click="showDirectory = !showDirectory"
                        class="glass-panel px-3.5 py-2 rounded-2xl text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('Staff List') }}</span>
                    <span class="px-1.5 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-[10px]" x-text="filteredTechnicians.length"></span>
                </button>

                {{-- Locate Me (GPS Sync) Button --}}
                <button @click="locateMyPosition()"
                        :disabled="locating"
                        title="{{ __('Sync My Current Device Location') }}"
                        class="glass-panel px-3 py-2 rounded-2xl text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4 text-emerald-500" :class="locating ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                    </svg>
                    <span class="hidden md:inline">{{ __('Locate Me') }}</span>
                </button>

                {{-- Refresh Button --}}
                <button @click="fetchLocations(true)"
                        :disabled="loading"
                        title="{{ __('Refresh Locations') }}"
                        class="glass-panel p-2 rounded-2xl text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <svg class="w-4 h-4" :class="loading ? 'animate-spin text-indigo-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>

        </div>

        {{-- FLOATING BOTTOM-LEFT STATUS LEGEND (Exact match with mobile app screenshot) --}}
        <div class="map-floating-legend glass-panel px-4 py-2 rounded-full flex items-center gap-3 text-xs font-semibold text-slate-600 dark:text-slate-300">
            <span class="flex items-center gap-1.5 cursor-pointer hover:opacity-80" @click="statusFilter = (statusFilter === 'online' ? '' : 'online')">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                <span :class="statusFilter === 'online' ? 'text-emerald-600 font-bold underline' : ''">Online</span>
            </span>
            <span class="flex items-center gap-1.5 cursor-pointer hover:opacity-80" @click="statusFilter = (statusFilter === 'idle' ? '' : 'idle')">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span>
                <span :class="statusFilter === 'idle' ? 'text-amber-600 font-bold underline' : ''">Idle</span>
            </span>
            <span class="flex items-center gap-1.5 cursor-pointer hover:opacity-80" @click="statusFilter = (statusFilter === 'offline' ? '' : 'offline')">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-400 shadow-sm"></span>
                <span :class="statusFilter === 'offline' ? 'text-slate-800 dark:text-white font-bold underline' : ''">Offline</span>
            </span>
            <template x-if="statusFilter">
                <button @click="statusFilter = ''" class="text-[10px] text-rose-500 hover:underline font-bold ml-1">✕ Clear</button>
            </template>
        </div>

        {{-- SLIDE-OVER STAFF DIRECTORY DRAWER (Hidden by default, slides out when clicked) --}}
        <div x-show="showDirectory"
             style="display: none;"
             class="map-drawer-panel"
             @click.away="showDirectory = false">
            
            {{-- Drawer Header --}}
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('Staff Directory') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Click staff to inspect profile & location') }}</p>
                </div>
                <button @click="showDirectory = false" class="p-1 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Search Input --}}
            <div class="p-3 border-b border-slate-100 dark:border-slate-800">
                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="{{ __('Search technician, phone, team…') }}"
                           class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Staff List Scroll --}}
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                <template x-for="tech in filteredTechnicians" :key="tech.user_id">
                    <div @click="selectTechnician(tech); showDirectory = false"
                         :class="selectedTechnician && selectedTechnician.user_id === tech.user_id ? 'bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                         class="p-3 transition-colors cursor-pointer flex items-center gap-3">
                        <div class="relative flex-shrink-0">
                            <img :src="tech.avatar_url"
                                 onerror="this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(this.getAttribute('alt') || 'User') + '&background=10b981&color=fff'"
                                 :alt="tech.name"
                                 class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-sm">
                            <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white dark:border-slate-800"
                                  :class="{
                                      'bg-emerald-500': tech.status === 'online',
                                      'bg-amber-500': tech.status === 'idle',
                                      'bg-slate-400': tech.status === 'offline'
                                  }"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" x-text="tech.name"></h4>
                                <span class="text-[10px] font-semibold text-slate-400" x-text="tech.last_seen_text"></span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                <span class="truncate" x-text="tech.team"></span>
                                <span>•</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-semibold" x-text="tech.active_tickets_count + ' tickets'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- BOTTOM SHEET / MODAL CARD (Matches App's _showDetail Bottom Sheet!) --}}
        <template x-if="selectedTechnician">
            <div class="map-detail-card" @click.away="selectedTechnician = null; clearHistoryTrail()">
                
                {{-- Sheet Header with Profile Photo, Name & Close Button --}}
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="relative">
                            <img :src="selectedTechnician.avatar_url"
                                 onerror="this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(this.getAttribute('alt') || 'User') + '&background=10b981&color=fff'"
                                 :alt="selectedTechnician.name"
                                 class="w-14 h-14 rounded-2xl object-cover border-2 border-emerald-500 shadow-md">
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-slate-800"
                                  :class="{
                                      'bg-emerald-500': selectedTechnician.status === 'online',
                                      'bg-amber-500': selectedTechnician.status === 'idle',
                                      'bg-slate-400': selectedTechnician.status === 'offline'
                                  }"></span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 leading-tight" x-text="selectedTechnician.name"></h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                      :class="{
                                          'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': selectedTechnician.status === 'online',
                                          'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': selectedTechnician.status === 'idle',
                                          'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300': selectedTechnician.status === 'offline'
                                      }"
                                      x-text="selectedTechnician.status"></span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium" x-text="selectedTechnician.role"></span>
                            </div>
                        </div>
                    </div>

                    <button @click="selectedTechnician = null; clearHistoryTrail()"
                            class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Detail Rows (Identical to Flutter _detailRow) --}}
                <div class="space-y-2 mb-4 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-2xl border border-slate-100 dark:border-slate-800 text-xs">
                    
                    {{-- Active Tickets --}}
                    <div class="flex items-center gap-2.5 text-amber-600 dark:text-amber-400 font-semibold">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                        <span x-text="selectedTechnician.active_tickets_count + ' {{ __('active tickets') }}'"></span>
                    </div>

                    {{-- Last Seen --}}
                    <div class="flex items-center gap-2.5 text-sky-600 dark:text-sky-400 font-medium">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('Last seen') }}: <span class="font-bold" x-text="selectedTechnician.last_seen_text"></span></span>
                    </div>

                    {{-- Battery Level --}}
                    <template x-if="selectedTechnician.battery_level !== null">
                        <div class="flex items-center gap-2.5 font-medium"
                             :class="selectedTechnician.battery_level < 20 ? 'text-rose-600 font-bold' : 'text-emerald-600 dark:text-emerald-400'">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>{{ __('Battery') }}: <span x-text="selectedTechnician.battery_level + '%'"></span></span>
                        </div>
                    </template>

                    {{-- Accuracy & Speed --}}
                    <div class="flex items-center gap-2.5 text-slate-500 dark:text-slate-400 font-medium">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span>{{ __('Speed') }}: <span x-text="(selectedTechnician.speed_kmh ?? '0') + ' km/h'"></span></span>
                        <template x-if="selectedTechnician.accuracy_meters">
                            <span>(±<span x-text="selectedTechnician.accuracy_meters"></span>m)</span>
                        </template>
                    </div>

                    {{-- Team & Shift --}}
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200 dark:border-slate-800 text-[11px] text-slate-500">
                        <span>{{ __('Team') }}: <strong class="text-slate-700 dark:text-slate-200" x-text="selectedTechnician.team"></strong></span>
                        <span>{{ __('Shift') }}: <strong class="text-slate-700 dark:text-slate-200" x-text="selectedTechnician.current_shift"></strong></span>
                    </div>
                </div>

                {{-- Action Buttons: Call, Zoom, GPS Trail --}}
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <template x-if="selectedTechnician.phone">
                        <a :href="'tel:' + selectedTechnician.phone"
                           class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ __('Call') }}</span>
                        </a>
                    </template>

                    <button @click="zoomToTechnician(selectedTechnician)"
                            class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span>{{ __('Zoom to') }}</span>
                    </button>
                </div>

                {{-- GPS Trail Toggle --}}
                <button @click="toggleHistoryTrail(selectedTechnician.user_id)"
                        :disabled="loadingTrail"
                        class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 text-xs font-semibold transition mb-3">
                    <svg class="w-3.5 h-3.5" :class="loadingTrail ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span x-text="showingTrail ? '{{ __('Hide Movement Trail') }}' : '{{ __('View 12h GPS Movement Trail') }}'"></span>
                </button>

                {{-- Assigned Active Tickets Preview --}}
                <div>
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                        {{ __('Assigned Tickets') }} (<span x-text="selectedTechnician.active_tickets_count"></span>)
                    </h4>
                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                        <template x-for="t in selectedTechnician.active_tickets" :key="t.id">
                            <a :href="t.url" target="_blank"
                               class="block p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-emerald-500 transition text-xs">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 text-[11px]" x-text="t.key"></span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase"
                                          :class="{
                                              'bg-red-100 text-red-700': t.priority === 'urgent' || t.priority === 'critical',
                                              'bg-amber-100 text-amber-700': t.priority === 'high',
                                              'bg-blue-100 text-blue-700': t.priority === 'medium',
                                              'bg-slate-100 text-slate-700': t.priority === 'low'
                                          }"
                                          x-text="t.priority"></span>
                                </div>
                                <p class="font-medium text-slate-700 dark:text-slate-200 truncate mt-0.5" x-text="t.title"></p>
                            </a>
                        </template>
                        <div x-show="selectedTechnician.active_tickets_count === 0" class="text-center py-2 text-xs text-slate-400">
                            {{ __('No active tickets assigned.') }}
                        </div>
                    </div>
                </div>

            </div>
        </template>

    </div>
</x-app-layout>
