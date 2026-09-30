import React, { useState, useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { 
    MapPin, Search, Navigation, X, Check, Compass, 
    Layers, Loader2, Info, Building2, Sparkles 
} from 'lucide-react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Politeknik Negeri Lampung (Base Camp / Tefa Hub)
const POLINELA_COORDS = { lat: -5.358245, lng: 105.234568 };

// Calculate distance in KM using Haversine formula
const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371; // Radius of Earth in km
    const dLat = (lat2 - lat1) * (Math.PI / 180);
    const dLon = (lon2 - lon1) * (Math.PI / 180);
    const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) * 
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return (R * c).toFixed(1);
};

const LocationPickerModal = ({ isOpen, onClose, onSelectLocation, initialCoords, currentAddress }) => {
    const mapContainerRef = useRef(null);
    const mapInstanceRef = useRef(null);
    const markerRef = useRef(null);
    const polylineRef = useRef(null);

    const [selectedCoords, setSelectedCoords] = useState(initialCoords || POLINELA_COORDS);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [isSearching, setIsSearching] = useState(false);
    const [isGeocoding, setIsGeocoding] = useState(false);
    const [isLocatingGPS, setIsLocatingGPS] = useState(false);
    const [addressDetails, setAddressDetails] = useState({
        displayName: currentAddress || 'Politeknik Negeri Lampung, Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung',
        road: '',
        suburb: '',
        city: 'Bandar Lampung',
        postcode: '35144',
    });

    const distanceKm = calculateDistance(
        POLINELA_COORDS.lat, POLINELA_COORDS.lng, 
        selectedCoords.lat, selectedCoords.lng
    );

    // Escape listener
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'Escape') onClose();
        };
        if (isOpen) {
            document.body.style.overflow = 'hidden';
            window.addEventListener('keydown', handleKeyDown);
        }
        return () => {
            document.body.style.overflow = 'unset';
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, [isOpen, onClose]);

    // Reverse Geocoding via Nominatim OpenStreetMap
    const reverseGeocode = async (lat, lng) => {
        setIsGeocoding(true);
        try {
            const res = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1`,
                { headers: { 'Accept-Language': 'id' } }
            );
            const data = await res.json();
            if (data && data.address) {
                const addr = data.address;
                const road = addr.road || addr.pedestrian || addr.street || addr.village || '';
                const suburb = addr.suburb || addr.neighbourhood || addr.quarter || '';
                const city = addr.city || addr.town || addr.municipality || addr.city_district || addr.county || 'Bandar Lampung';
                const postcode = addr.postcode || '';

                let formatted = '';
                if (road) formatted += road;
                if (suburb && !formatted.includes(suburb)) formatted += (formatted ? ', ' : '') + suburb;
                if (city && !formatted.includes(city)) formatted += (formatted ? ', ' : '') + city;
                if (!formatted) formatted = data.display_name;

                setAddressDetails({
                    displayName: formatted || data.display_name,
                    road: road,
                    suburb: suburb,
                    city: city,
                    postcode: postcode,
                });
            }
        } catch (err) {
            console.error('Reverse geocode error:', err);
        } finally {
            setIsGeocoding(false);
        }
    };

    const [mapLayerType, setMapLayerType] = useState('google_streets'); // 'google_streets' | 'google_hybrid' | 'osm'
    const tileLayerRef = useRef(null);

    // Initialize Map when modal opens
    useEffect(() => {
        if (!isOpen) return;

        const initTimer = setTimeout(() => {
            if (!mapContainerRef.current) return;

            if (!mapInstanceRef.current) {
                // Initialize map
                const map = L.map(mapContainerRef.current, {
                    center: [selectedCoords.lat, selectedCoords.lng],
                    zoom: 16,
                    zoomControl: false,
                    maxZoom: 20,
                });

                // Add zoom control top-right
                L.control.zoom({ position: 'topright' }).addTo(map);

                // Google Maps Tile Layer (Super fast & reliable, no rate-limiting)
                const googleTileLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}&hl=id', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Maps',
                }).addTo(map);

                tileLayerRef.current = googleTileLayer;

                // Polinela Campus Hub Marker
                const campusIcon = L.divIcon({
                    className: 'custom-campus-pin',
                    html: `
                        <div style="background: #15803d; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(21,128,61,0.5); border: 2.5px solid white;">
                            <span style="font-size: 16px;">🏫</span>
                        </div>
                    `,
                    iconSize: [34, 34],
                    iconAnchor: [17, 17],
                });
                L.marker([POLINELA_COORDS.lat, POLINELA_COORDS.lng], { icon: campusIcon })
                    .addTo(map)
                    .bindPopup('<b>Politeknik Negeri Lampung</b><br/>Pusat Tefa & Pengiriman Agro');

                // Customer Destination Draggable Marker
                const customerIcon = L.divIcon({
                    className: 'custom-customer-pin',
                    html: `
                        <div style="position: relative; width: 40px; height: 48px;">
                            <div style="background: #dc2626; color: white; width: 38px; height: 38px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(220,38,38,0.45); border: 3px solid white; position: absolute; top: 0; left: 1px;">
                                <span style="transform: rotate(45deg); font-size: 16px;">📍</span>
                            </div>
                        </div>
                    `,
                    iconSize: [40, 48],
                    iconAnchor: [20, 46],
                });

                const marker = L.marker([selectedCoords.lat, selectedCoords.lng], {
                    draggable: true,
                    icon: customerIcon,
                }).addTo(map);

                // Connect with dashed line to Polinela
                const polyline = L.polyline(
                    [
                        [POLINELA_COORDS.lat, POLINELA_COORDS.lng],
                        [selectedCoords.lat, selectedCoords.lng]
                    ],
                    { color: '#16a34a', weight: 3, dashArray: '6, 8', opacity: 0.75 }
                ).addTo(map);

                marker.on('dragend', () => {
                    const pos = marker.getLatLng();
                    setSelectedCoords({ lat: pos.lat, lng: pos.lng });
                    polyline.setLatLngs([
                        [POLINELA_COORDS.lat, POLINELA_COORDS.lng],
                        [pos.lat, pos.lng]
                    ]);
                    reverseGeocode(pos.lat, pos.lng);
                });

                map.on('click', (e) => {
                    const { lat, lng } = e.latlng;
                    marker.setLatLng([lat, lng]);
                    setSelectedCoords({ lat, lng });
                    polyline.setLatLngs([
                        [POLINELA_COORDS.lat, POLINELA_COORDS.lng],
                        [lat, lng]
                    ]);
                    reverseGeocode(lat, lng);
                });

                mapInstanceRef.current = map;
                markerRef.current = marker;
                polylineRef.current = polyline;

                // Force layout recalculations to guarantee map tiles render instantly
                map.invalidateSize();
                setTimeout(() => map.invalidateSize(), 200);
                setTimeout(() => map.invalidateSize(), 500);
            } else {
                mapInstanceRef.current.invalidateSize();
                setTimeout(() => mapInstanceRef.current?.invalidateSize(), 200);
            }
        }, 80);

        return () => clearTimeout(initTimer);
    }, [isOpen]);

    // Handle Switch Map Layer Type (Google Maps, Satellite Hybrid, OSM)
    const handleSwitchMapLayer = (type) => {
        if (!mapInstanceRef.current) return;
        setMapLayerType(type);

        if (tileLayerRef.current) {
            mapInstanceRef.current.removeLayer(tileLayerRef.current);
        }

        let newLayer;
        if (type === 'google_hybrid') {
            newLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}&hl=id', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps Satellite',
            });
        } else if (type === 'osm') {
            newLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            });
        } else {
            // Google Standard Roads
            newLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}&hl=id', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps',
            });
        }

        newLayer.addTo(mapInstanceRef.current);
        tileLayerRef.current = newLayer;
        mapInstanceRef.current.invalidateSize();
    };

    // Handle Search Location input with Nominatim
    const handleSearch = async (e) => {
        e.preventDefault();
        if (!searchQuery.trim()) return;

        setIsSearching(true);
        try {
            const res = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&countrycodes=id&limit=5`,
                { headers: { 'Accept-Language': 'id' } }
            );
            const data = await res.json();
            setSearchResults(data || []);
        } catch (err) {
            console.error('Search location error:', err);
        } finally {
            setIsSearching(false);
        }
    };

    const handleSelectSearchResult = (result) => {
        const lat = parseFloat(result.lat);
        const lng = parseFloat(result.lon);

        setSelectedCoords({ lat, lng });
        setSearchResults([]);
        setSearchQuery('');

        if (mapInstanceRef.current && markerRef.current && polylineRef.current) {
            mapInstanceRef.current.flyTo([lat, lng], 16, { duration: 1.2 });
            markerRef.current.setLatLng([lat, lng]);
            polylineRef.current.setLatLngs([
                [POLINELA_COORDS.lat, POLINELA_COORDS.lng],
                [lat, lng]
            ]);
        }

        reverseGeocode(lat, lng);
    };

    // Geolocation Browser (Current GPS Location)
    const handleUseCurrentLocation = () => {
        if (!navigator.geolocation) {
            alert('Perangkat/browser Anda tidak mendukung fitur deteksi lokasi otomatis (GPS).');
            return;
        }

        setIsLocatingGPS(true);
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                setSelectedCoords({ lat, lng });
                setIsLocatingGPS(false);

                if (mapInstanceRef.current && markerRef.current && polylineRef.current) {
                    mapInstanceRef.current.flyTo([lat, lng], 17, { duration: 1.5 });
                    markerRef.current.setLatLng([lat, lng]);
                    polylineRef.current.setLatLngs([
                        [POLINELA_COORDS.lat, POLINELA_COORDS.lng],
                        [lat, lng]
                    ]);
                }
                reverseGeocode(lat, lng);
            },
            (err) => {
                setIsLocatingGPS(false);
                alert('Gagal mendeteksi lokasi GPS. Pastikan izin akses lokasi telah Anda izinkan pada browser.');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    };

    // Confirm and pass data back
    const handleConfirm = () => {
        if (onSelectLocation) {
            onSelectLocation({
                lat: selectedCoords.lat,
                lng: selectedCoords.lng,
                alamat: addressDetails.displayName,
                kota: addressDetails.city,
                kodePos: addressDetails.postcode,
                distanceKm: distanceKm,
            });
        }
        onClose();
    };

    if (!isOpen) return null;

    return createPortal(
        <div 
            className="location-modal-backdrop position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-2 p-md-4"
            style={{ 
                backgroundColor: 'rgba(15, 23, 42, 0.85)', 
                backdropFilter: 'blur(10px)', 
                zIndex: 999999,
                overflowY: 'auto'
            }}
            onClick={(e) => {
                if (e.target === e.currentTarget) onClose();
            }}
        >
            <div 
                className="location-modal-dialog bg-white text-dark rounded-4 shadow-2xl overflow-hidden d-flex flex-column my-auto animate-fade-in"
                style={{ 
                    width: '100%', 
                    maxWidth: '920px', 
                    height: '88vh',
                    maxHeight: '750px',
                    position: 'relative' 
                }}
            >
                {/* Header */}
                <div className="d-flex align-items-center justify-content-between px-4 py-3 border-bottom bg-white">
                    <div className="d-flex align-items-center gap-2">
                        <div className="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style={{ width: '36px', height: '36px' }}>
                            <Compass size={18} />
                        </div>
                        <div>
                            <h6 className="fw-bold text-dark mb-0">Tentukan Titik Lokasi Pengiriman (Pin Point)</h6>
                            <small className="text-muted">Geser pin merah di peta atau cari nama jalan/tempat Anda.</small>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        onClick={onClose}
                        className="btn btn-outline-secondary btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center"
                        style={{ width: '32px', height: '32px' }}
                    >
                        <X size={18} />
                    </button>
                </div>

                {/* Map & Search Container */}
                <div className="position-relative flex-grow-1 w-100" style={{ minHeight: '320px' }}>
                    
                    {/* Floating Search Bar */}
                    <div className="position-absolute top-0 start-0 end-0 p-3" style={{ zIndex: 1000, maxWidth: '460px' }}>
                        <form onSubmit={handleSearch} className="position-relative shadow-lg rounded-pill">
                            <input 
                                type="text"
                                className="form-control rounded-pill ps-4 pe-5 py-2 border-0 bg-white"
                                placeholder="Cari nama jalan, kelurahan, atau gedung..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                style={{ boxShadow: '0 4px 20px rgba(0,0,0,0.15)', fontSize: '0.9rem' }}
                            />
                            <button 
                                type="submit" 
                                disabled={isSearching}
                                className="btn btn-link position-absolute end-0 top-50 translate-middle-y text-success pe-3"
                            >
                                {isSearching ? <Loader2 size={18} className="animate-spin" /> : <Search size={18} />}
                            </button>
                        </form>

                        {/* Search Autocomplete Results Dropdown */}
                        {searchResults.length > 0 && (
                            <div className="bg-white rounded-4 shadow-xl border mt-2 overflow-hidden py-1">
                                {searchResults.map((item, idx) => (
                                    <button
                                        key={idx}
                                        type="button"
                                        onClick={() => handleSelectSearchResult(item)}
                                        className="btn btn-link text-start text-dark w-100 px-3 py-2 border-bottom last-border-none text-decoration-none d-flex align-items-start gap-2 hover-bg-light"
                                        style={{ fontSize: '0.85rem' }}
                                    >
                                        <MapPin size={16} className="text-danger flex-shrink-0 mt-1" />
                                        <span className="text-truncate">{item.display_name}</span>
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Floating GPS Button */}
                    <div className="position-absolute bottom-0 end-0 p-3" style={{ zIndex: 1000 }}>
                        <button
                            type="button"
                            onClick={handleUseCurrentLocation}
                            disabled={isLocatingGPS}
                            className="btn btn-light rounded-pill shadow-lg px-3 py-2 fw-semibold d-flex align-items-center gap-2 border text-success"
                            style={{ backgroundColor: '#ffffff', boxShadow: '0 4px 16px rgba(0,0,0,0.2)' }}
                        >
                            {isLocatingGPS ? (
                                <>
                                    <Loader2 size={16} className="animate-spin text-success" />
                                    <span className="small">Mencari GPS...</span>
                                </>
                            ) : (
                                <>
                                    <Navigation size={16} className="text-success" />
                                    <span className="small">Lokasi Saya Saat Ini</span>
                                </>
                            )}
                        </button>
                    </div>

                    {/* Floating Map Layer Switcher (Google Maps, Satelit, OpenStreet) */}
                    <div className="position-absolute bottom-0 start-0 p-3" style={{ zIndex: 1000 }}>
                        <div className="btn-group bg-white rounded-pill shadow-lg p-1 border" role="group">
                            <button
                                type="button"
                                onClick={() => handleSwitchMapLayer('google_streets')}
                                className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold transition-all ${mapLayerType === 'google_streets' ? 'btn-success text-white shadow-sm' : 'btn-light text-muted'}`}
                                style={{ fontSize: '12px' }}
                            >
                                🗺️ Google Maps
                            </button>
                            <button
                                type="button"
                                onClick={() => handleSwitchMapLayer('google_hybrid')}
                                className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold transition-all ${mapLayerType === 'google_hybrid' ? 'btn-success text-white shadow-sm' : 'btn-light text-muted'}`}
                                style={{ fontSize: '12px' }}
                            >
                                🛰️ Satelit Google
                            </button>
                            <button
                                type="button"
                                onClick={() => handleSwitchMapLayer('osm')}
                                className={`btn btn-sm rounded-pill px-3 py-1 fw-semibold transition-all ${mapLayerType === 'osm' ? 'btn-success text-white shadow-sm' : 'btn-light text-muted'}`}
                                style={{ fontSize: '12px' }}
                            >
                                🌐 OpenStreet
                            </button>
                        </div>
                    </div>

                    {/* Leaflet Map Canvas */}
                    <div ref={mapContainerRef} className="w-100 h-100" style={{ zIndex: 1, minHeight: '340px' }} />
                </div>

                {/* Footer Bottom Bar (Selected Address & Confirmation) */}
                <div className="p-3 p-md-4 border-top bg-white">
                    <div className="row g-3 align-items-center">
                        <div className="col-md-8">
                            <div className="d-flex align-items-start gap-2">
                                <div className="p-2 bg-danger bg-opacity-10 text-danger rounded-circle flex-shrink-0 mt-1">
                                    <MapPin size={18} />
                                </div>
                                <div className="overflow-hidden">
                                    <div className="d-flex align-items-center gap-2 mb-1">
                                        <span className="fw-bold text-dark small">Alamat Terpilih:</span>
                                        <span className="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill" style={{ fontSize: '11px' }}>
                                            📏 ± {distanceKm} km dari Kampus Polinela
                                        </span>
                                    </div>
                                    <div className="text-dark small text-truncate fw-medium" title={addressDetails.displayName}>
                                        {isGeocoding ? (
                                            <span className="text-muted d-flex align-items-center gap-1">
                                                <Loader2 size={13} className="animate-spin" /> Mengambil detail alamat...
                                            </span>
                                        ) : (
                                            addressDetails.displayName
                                        )}
                                    </div>
                                    <div className="text-muted" style={{ fontSize: '0.75rem' }}>
                                        Koordinat: {selectedCoords.lat.toFixed(5)}, {selectedCoords.lng.toFixed(5)} • {addressDetails.city || 'Lampung'} {addressDetails.postcode ? `(${addressDetails.postcode})` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="col-md-4 text-md-end d-flex gap-2 justify-content-end">
                            <button
                                type="button"
                                onClick={onClose}
                                className="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                onClick={handleConfirm}
                                className="btn btn-success rounded-pill px-4 py-2 fw-bold btn-sm d-flex align-items-center gap-1 shadow-sm"
                            >
                                <Check size={16} /> Gunakan Lokasi Ini
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>,
        document.body
    );
};

export default LocationPickerModal;
