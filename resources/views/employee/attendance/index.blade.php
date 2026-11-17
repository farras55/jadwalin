<x-app-layout>
    {{-- Leaflet Map CSS & JS for Real-Time Geofencing Map --}}
    <link rel="stylesheet" href="{{ config('jadwalin.assets.leaflet_css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css') }}" crossorigin="" />
    <script src="{{ config('jadwalin.assets.leaflet_js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js') }}" crossorigin=""></script>

    @php
        $user = auth()->user();
        $userName = $user?->name ?? 'Karyawan';
        $userRole = $user?->position?->name ?? ($user?->role ? ucfirst($user->role) : 'Staff');
        $userCode = $user ? 'KRY-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'KRY-0001';
        
        // Inisial user untuk avatar
        $initials = collect(explode(' ', $userName))
            ->filter()
            ->map(fn($segment) => mb_substr($segment, 0, 1))
            ->take(2)
            ->join('');
        if (empty($initials)) {
            $initials = 'KY';
        }

        // State UI ditentukan sepenuhnya dari backend (Controller)
        // $attendanceState: 'idle' | 'working' | 'completed' | 'no_shift'
        // $isClockedIn, $isClockedOut, $shiftEnded, $todayLateFormatted dari Controller

        // Outlet & Summary stats dinamis dari Controller (Single Source of Truth)
        $outletInfo = $outlet;
        $outletName = $outletInfo['name'] ?? 'Kantor Utama';
        $outletAddress = $outletInfo['address'] ?? 'Alamat kantor belum dikonfigurasi';
        $toleranceMin = $toleranceMinutes ?? 5;

        $stats = $attendanceStats ?? [
            'attendance_percentage' => 96.4,
            'present_shifts' => 21,
            'total_shifts' => 22,
            'on_time_days' => 19,
            'on_time_status' => 'On-Track',
            'total_work_hours' => 168,
        ];

        $tz = auth()->user()?->company?->timezone ?? config('app.timezone', 'Asia/Jakarta');
        $tzAbbr = \App\Models\AuditLog::getTimezoneAbbreviation($tz);

        $shiftTitle = $todayShift && $todayShift->shift ? $todayShift->shift->title : 'Tidak Ada Shift';
        $shiftStart = $todayShift && $todayShift->shift ? \Carbon\Carbon::parse($todayShift->shift->start_time)->format('H:i') : '--:--';
        $shiftEnd   = $todayShift && $todayShift->shift ? \Carbon\Carbon::parse($todayShift->shift->end_time)->format('H:i') : '--:--';
        $clockInFormatted  = $isClockedIn  ? \Carbon\Carbon::parse($todayShift->clock_in_time)->format('H:i') . ' ' . $tzAbbr : '-';
        $clockOutFormatted = $isClockedOut ? \Carbon\Carbon::parse($todayShift->clock_out_time)->format('H:i') . ' ' . $tzAbbr : '-';

        // Hitung target durasi shift secara dinamis (fallback ke 8 jam jika tidak ada jadwal shift)
        $targetShiftSeconds = 8 * 3600;
        $targetShiftHours = 8.0;
        $targetShiftFormatted = '08:00:00';
        if ($todayShift && $todayShift->shift) {
            $sStart = \Carbon\Carbon::parse($todayShift->shift->start_time);
            $sEnd = \Carbon\Carbon::parse($todayShift->shift->end_time);
            if ($sEnd->lessThan($sStart)) {
                $sEnd->addDay();
            }
            $diffSec = $sStart->diffInSeconds($sEnd);
            if ($diffSec > 0) {
                $targetShiftSeconds = $diffSec;
                $targetShiftHours = round($diffSec / 3600, 1);
                $th = floor($diffSec / 3600);
                $tm = floor(($diffSec % 3600) / 60);
                $ts = $diffSec % 60;
                $targetShiftFormatted = sprintf('%02d:%02d:%02d', $th, $tm, $ts);
            }
        }
        $allowMockGps = (bool) config('jadwalin.attendance.allow_mock_gps', true);

        // Dataset Riwayat 7 Hari Terakhir
        $historyData = $recentHistory->isNotEmpty() ? $recentHistory->map(function($item) use ($tz, $tzAbbr) {
            $startTime = \Carbon\Carbon::parse($item->shift->start_time ?? now());
            $dateStr = $startTime->translatedFormat('d M Y');
            $dayStr = $startTime->translatedFormat('l');
            $shiftName = $item->shift->title ?? 'Shift Regular';
            $outlet = $item->shift->department->name ?? 'Operasional';
            $cIn = $item->clock_in_time ? \Carbon\Carbon::parse($item->clock_in_time)->format('H:i') . ' ' . $tzAbbr : '-';
            $cOut = $item->clock_out_time ? \Carbon\Carbon::parse($item->clock_out_time)->format('H:i') . ' ' . $tzAbbr : '-';
            
            $durationStr = '-';
            if ($item->clock_in_time && $item->clock_out_time) {
                $diff = \Carbon\Carbon::parse($item->clock_in_time)->diffInMinutes($item->clock_out_time);
                $h = floor($diff / 60);
                $m = $diff % 60;
                $durationStr = "{$h} Jam " . str_pad($m, 2, '0', STR_PAD_LEFT) . " Menit";
            } elseif ($item->clock_in_time) {
                $durationStr = 'Sedang Berjalan';
            }

            $rawStatus = $item->status ?? 'present';
            $lateMin = (int) ($item->late_minutes ?? 0);

            // Format durasi keterlambatan: Jam + Menit
            $lateH = (int) floor($lateMin / 60);
            $lateM = $lateMin % 60;
            $lateLabel = $lateH > 0
                ? "Terlambat ({$lateH} Jam " . str_pad($lateM, 2, '0', STR_PAD_LEFT) . " Menit)"
                : ($lateMin > 0 ? "Terlambat ({$lateMin} Menit)" : 'Terlambat');

            $hasClockedIn = ! empty($item->clock_in_time);
            $hasClockedOut = ! empty($item->clock_out_time);

            $shiftEnded = false;
            if ($item->shift) {
                $shiftEndDateTime = \App\Console\Commands\AutoClockOutCommand::resolveShiftEndDateTime($item->shift, $tz);
                $shiftEnded = \Carbon\Carbon::now($tz)->greaterThan($shiftEndDateTime);
            }

            if ($hasClockedIn) {
                if ($rawStatus === 'late' || $lateMin > 0) {
                    $statusKey = 'terlambat';
                    $statusLabel = $lateLabel;
                    $badgeTheme = 'rose';
                } elseif ($hasClockedOut) {
                    $statusKey = 'hadir';
                    $statusLabel = 'Hadir Tepat Waktu';
                    $badgeTheme = 'emerald';
                } else {
                    $statusKey = 'hadir';
                    $statusLabel = 'Sedang Bekerja';
                    $badgeTheme = 'teal';
                }
            } else {
                // Belum melakukan clock-in
                if ($rawStatus === 'leave') {
                    $statusKey = 'off';
                    $statusLabel = 'Izin / Cuti';
                    $badgeTheme = 'indigo';
                } elseif ($rawStatus === 'absent' || $shiftEnded) {
                    $statusKey = 'off';
                    $statusLabel = 'Tidak Hadir';
                    $badgeTheme = 'rose';
                } else {
                    $statusKey = 'belum_hadir';
                    $statusLabel = 'Belum Hadir';
                    $badgeTheme = 'amber';
                }
            }

            return [
                'id' => $item->id,
                'date' => $dateStr,
                'day' => $dayStr,
                'shift' => $shiftName,
                'outlet' => $outlet,
                'clock_in' => $cIn,
                'clock_out' => $cOut,
                'duration' => $durationStr,
                'status_key' => $statusKey,
                'status_label' => $statusLabel,
                'badge_theme' => $badgeTheme,
                'is_late' => $rawStatus === 'late' || $lateMin > 0,
                'is_off' => $rawStatus === 'absent' || $rawStatus === 'leave',
                'is_auto_clockout' => (bool) $item->is_auto_clockout,
                'overtime_status' => $item->overtime_status,
                'can_regularize' => (bool) ($item->is_auto_clockout || ($item->clock_out_time && $item->shift && \Carbon\Carbon::parse($item->clock_out_time)->gt(\App\Console\Commands\AutoClockOutCommand::resolveShiftEndDateTime($item->shift, $tz)))),
                'regularization_status' => $item->latestAttendanceRegularization?->status,
                'shift_end_datetime' => $item->shift ? \App\Console\Commands\AutoClockOutCommand::resolveShiftEndDateTime($item->shift, $tz)->format('Y-m-d\TH:i') : '',
                'shift_end_formatted' => $item->shift ? \App\Console\Commands\AutoClockOutCommand::resolveShiftEndDateTime($item->shift, $tz)->format('H:i') . ' ' . $tzAbbr : '',
            ];
        }) : collect([]);
    @endphp

    {{-- Definisi function attendancePage di awal sebelum Alpine mem-parse DOM --}}
    <script>
        window.attendancePage = function(config) {
            const hasInitialCoords = config.officeLatitude !== null && config.officeLatitude !== undefined && config.officeLatitude !== '' && !isNaN(parseFloat(config.officeLatitude));
            const refLatitude = hasInitialCoords ? parseFloat(config.officeLatitude) : null;
            const refLongitude = (config.officeLongitude !== null && config.officeLongitude !== undefined && config.officeLongitude !== '' && !isNaN(parseFloat(config.officeLongitude)))
                ? parseFloat(config.officeLongitude)
                : null;
            const refRadius = parseInt(config.attendanceRadius || config.outlet?.radius || 100);
            const isConfigured = Boolean(config.isLocationConfigured && hasInitialCoords && refLongitude !== null);

            return {
                activeMode: config.initialMode || 'idle',
                filterStatus: 'all',
                sopModalOpen: false,
                regularizationModalOpen: false,
                submittingRegularization: false,
                regularizationError: '',
                regularizationSuccess: '',
                lateClockOutModalOpen: false,
                lateClockOutConfirmed: false,
                lateClockOutNotes: '',
                isPastShiftEnd: {{ $shiftEnded ? 'true' : 'false' }},
                regularizationForm: {
                    shift_assignment_id: null,
                    shift_title: '',
                    shift_date: '',
                    shift_end_datetime: '',
                    shift_end_formatted: '',
                    requested_clock_out_time: '',
                    reason: ''
                },
                targetShiftSeconds: parseInt(config.targetShiftSeconds || 28800),
                targetShiftHours: parseFloat(config.targetShiftHours || 8.0),
                allowMockGps: Boolean(config.allowMockGps !== undefined ? config.allowMockGps : true),
                timerDisplay: '00 : 00 : 00',
                progressPercent: 0,
                progressHoursText: '0.0 / ' + (parseFloat(config.targetShiftHours || 8.0).toFixed(1)) + ' Jam',
                timerInterval: null,
                pollInterval: null,
                totalElapsedSeconds: 0,

                // Parameter Geofencing Dinamis dari Database (Single Source of Truth)
                isLocationConfigured: isConfigured,
                officeLatitude: refLatitude,
                officeLongitude: refLongitude,
                attendanceRadius: refRadius,

                // Geolocation & Outlet Map States
                outletInfo: config.outlet || {
                    name: '{{ $outletName }}',
                    address: '{{ $outletAddress }}',
                    latitude: refLatitude,
                    longitude: refLongitude,
                    radius: refRadius,
                    is_configured: isConfigured
                },
                userLat: (isConfigured && refLatitude !== null && Boolean(config.allowMockGps !== undefined ? config.allowMockGps : true)) ? refLatitude + 0.00008 : null,
                userLng: (isConfigured && refLongitude !== null && Boolean(config.allowMockGps !== undefined ? config.allowMockGps : true)) ? refLongitude + 0.00008 : null,
                distanceMeters: null,
                isInsideRadius: isConfigured,
                locationStatus: isConfigured ? 'success' : 'unconfigured',
                map: null,
                markers: {
                    outlet: null,
                    radius: null,
                    user: null
                },

                // Teks status reaktif
                distanceText: isConfigured ? 'Mendeteksi...' : 'Lokasi Belum Diatur',
                radiusNotificationText: isConfigured ? 'Menghubungkan ke titik presensi...' : 'Lokasi kerja belum diatur oleh admin di Pengaturan Platform.',
                verificationStatusText: isConfigured ? 'Lokasi Presensi Terverifikasi' : 'Lokasi Belum Diatur',
                accuracyStatusText: isConfigured ? 'Di Dalam Radius Aman' : 'Lokasi Belum Diatur',
                connectionStatusText: isConfigured ? 'Terhubung' : 'Lokasi Belum Diatur',

                init() {
                    if (config.initialMode === 'working') {
                        this.startWorkingTimer(config.clockInTime);
                    } else if (config.initialMode === 'completed') {
                        if (config.clockInTime && config.clockOutTime) {
                            const inMs  = new Date(config.clockInTime).getTime();
                            const outMs = new Date(config.clockOutTime).getTime();
                            this.totalElapsedSeconds = Math.max(0, Math.floor((outMs - inMs) / 1000));
                        } else {
                            this.totalElapsedSeconds = 0;
                        }
                        this.updateWorkingUI();
                    } else {
                        this.timerDisplay = '00 : 00 : 00';
                        this.progressPercent = 0;
                        this.progressHoursText = '0.0 / ' + (this.targetShiftHours || 8.0).toFixed(1) + ' Jam';
                    }

                    this.calculateSafePosition();

                    this.$nextTick(() => {
                        this.initMap();
                        this.startLocationPolling();
                    });

                    window.addEventListener('resize', () => {
                        if (this.map) {
                            setTimeout(() => this.map.invalidateSize(), 200);
                        }
                    });

                    window.addEventListener('beforeunload', () => {
                        if (this.pollInterval) clearInterval(this.pollInterval);
                    });
                },

                _applyWorkingTimer(clockInIso) {
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.startWorkingTimer(clockInIso);
                },

                startWorkingTimer(clockInIso) {
                    if (clockInIso) {
                        const start = new Date(clockInIso).getTime();
                        const update = () => {
                            const now = new Date().getTime();
                            const diffSecs = Math.max(0, Math.floor((now - start) / 1000));
                            this.totalElapsedSeconds = diffSecs;
                            this.updateWorkingUI();
                        };
                        update();
                        this.timerInterval = setInterval(update, 1000);
                    } else {
                        if (this.timerInterval) clearInterval(this.timerInterval);
                        this.timerInterval = setInterval(() => {
                            this.totalElapsedSeconds++;
                            this.updateWorkingUI();
                        }, 1000);
                    }
                },

                updateWorkingUI() {
                    const hours = Math.floor(this.totalElapsedSeconds / 3600);
                    const mins = Math.floor((this.totalElapsedSeconds % 3600) / 60);
                    const secs = this.totalElapsedSeconds % 60;
                    this.timerDisplay = 
                        String(hours).padStart(2, '0') + ' : ' + 
                        String(mins).padStart(2, '0') + ' : ' + 
                        String(secs).padStart(2, '0');

                    const targetSec = this.targetShiftSeconds || 28800;
                    const targetHrs = (this.targetShiftHours || 8.0).toFixed(1);
                    const pct = Math.min(100, Math.round((this.totalElapsedSeconds / targetSec) * 100));
                    this.progressPercent = pct;

                    const elapsedHours = (this.totalElapsedSeconds / 3600).toFixed(1);
                    this.progressHoursText = elapsedHours + ' / ' + targetHrs + ' Jam';
                },

                calculateSafePosition() {
                    const hasCoords = this.officeLatitude !== null && 
                                      this.officeLongitude !== null && 
                                      !isNaN(parseFloat(this.officeLatitude)) && 
                                      !isNaN(parseFloat(this.officeLongitude));

                    if (hasCoords) {
                        const cleanLat = parseFloat(this.officeLatitude);
                        const cleanLng = parseFloat(this.officeLongitude);
                        if (this.allowMockGps) {
                            // Titik presensi aman karyawan diturunkan dari titik kantor di database (+ ~12-13 meter)
                            this.userLat = cleanLat + 0.00008;
                            this.userLng = cleanLng + 0.00008;
                            this.locationStatus = 'success';
                            this.isLocationConfigured = true;

                            const dist = this.calculateDistance(this.userLat, this.userLng, cleanLat, cleanLng);
                            this.distanceMeters = dist;
                            this.isInsideRadius = (dist !== null && dist <= this.attendanceRadius);
                        } else {
                            this.locationStatus = 'detecting';
                            this.isLocationConfigured = true;
                        }
                    } else {
                        this.userLat = null;
                        this.userLng = null;
                        this.distanceMeters = null;
                        this.isInsideRadius = false;
                        this.locationStatus = 'unconfigured';
                        this.isLocationConfigured = false;
                    }

                    this.syncLocationTexts();
                },

                startLocationPolling() {
                    if (this.pollInterval) clearInterval(this.pollInterval);
                    // Polling setiap 15 detik untuk sinkronisasi cepat jika lokasi diubah oleh Super Admin
                    this.pollInterval = setInterval(() => {
                        this.fetchLatestLocation();
                    }, 15000);
                },

                async fetchLatestLocation() {
                    try {
                        const response = await fetch('{{ route('api.employee.attendance-location') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!response.ok) return;
                        const data = await response.json();
                        this.applyLocationUpdate(data);
                    } catch (e) {
                        console.warn('Gagal sinkronisasi lokasi kerja:', e);
                    }
                },

                applyLocationUpdate(data) {
                    if (!data) return;

                    const hasCoords = data.latitude !== null && data.longitude !== null && !isNaN(parseFloat(data.latitude)) && !isNaN(parseFloat(data.longitude));
                    const newLat = hasCoords ? parseFloat(data.latitude) : null;
                    const newLng = hasCoords ? parseFloat(data.longitude) : null;
                    const newRadius = parseInt(data.radius_meters || 100);

                    const changed = (this.officeLatitude !== newLat) ||
                                    (this.officeLongitude !== newLng) ||
                                    (this.attendanceRadius !== newRadius) ||
                                    (this.isLocationConfigured !== hasCoords) ||
                                    (this.outletInfo.name !== data.name) ||
                                    (this.outletInfo.address !== data.address);

                    if (changed) {
                        this.officeLatitude = newLat;
                        this.officeLongitude = newLng;
                        this.attendanceRadius = newRadius;
                        this.isLocationConfigured = hasCoords;
                        this.outletInfo = {
                            id: data.id,
                            name: data.name,
                            address: data.address,
                            latitude: newLat,
                            longitude: newLng,
                            radius: newRadius,
                            is_configured: hasCoords
                        };

                        this.calculateSafePosition();

                        if (this.map && typeof L !== 'undefined') {
                            if (hasCoords) {
                                this.renderOfficeMarker(newLat, newLng, newRadius);
                                this.updateUserMarker(this.userLat, this.userLng);
                                this.updateMapBounds();
                            } else {
                                if (this.markers.outlet) {
                                    this.map.removeLayer(this.markers.outlet);
                                    this.markers.outlet = null;
                                }
                                if (this.markers.radius) {
                                    this.map.removeLayer(this.markers.radius);
                                    this.markers.radius = null;
                                }
                                if (this.markers.user) {
                                    this.map.removeLayer(this.markers.user);
                                    this.markers.user = null;
                                }
                                this.map.setView([-2.5489, 118.0149], 5);
                            }
                        }
                    }
                },

                syncLocationTexts() {
                    if (!this.isLocationConfigured || this.officeLatitude === null || this.officeLongitude === null) {
                        this.distanceText = 'Lokasi Belum Diatur';
                        this.radiusNotificationText = 'Lokasi kerja belum diatur oleh admin di Pengaturan Platform.';
                        this.connectionStatusText = 'Lokasi Belum Diatur';
                        this.accuracyStatusText = 'Lokasi Belum Diatur';
                        this.verificationStatusText = 'Lokasi Belum Diatur';
                        return;
                    }

                    if (this.distanceMeters === null) {
                        this.distanceText = 'Mendeteksi...';
                    } else if (this.distanceMeters < 1000) {
                        this.distanceText = this.distanceMeters + ' Meter';
                    } else {
                        this.distanceText = (this.distanceMeters / 1000).toFixed(1) + ' km';
                    }

                    this.radiusNotificationText = 'Berada ' + this.distanceText + ' dari titik presensi ' + (this.outletInfo.name || 'Lokasi Kerja');
                    this.connectionStatusText = 'Terhubung';
                    this.verificationStatusText = 'Lokasi Presensi Terverifikasi';
                    this.accuracyStatusText = this.isInsideRadius ? 'Di Dalam Radius Aman' : 'Di Luar Radius Aman';
                },

                initMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initMap(), 250);
                        return;
                    }

                    const mapContainer = document.getElementById('attendance-map');
                    if (!mapContainer || this.map) return;

                    const hasOfficeCoords = this.officeLatitude !== null && 
                                            this.officeLongitude !== null && 
                                            !isNaN(parseFloat(this.officeLatitude)) && 
                                            !isNaN(parseFloat(this.officeLongitude));
                    const initialLat = hasOfficeCoords ? parseFloat(this.officeLatitude) : -2.5489;
                    const initialLng = hasOfficeCoords ? parseFloat(this.officeLongitude) : 118.0149;
                    const zoomLevel = hasOfficeCoords ? 17 : 5;

                    this.map = L.map('attendance-map', {
                        center: [initialLat, initialLng],
                        zoom: zoomLevel,
                        zoomControl: false,
                        attributionControl: false
                    });

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                    }).addTo(this.map);

                    L.control.zoom({ position: 'topright' }).addTo(this.map);

                    if (hasOfficeCoords) {
                        this.renderOfficeMarker(initialLat, initialLng, parseInt(this.attendanceRadius) || 100);
                        if (this.userLat && this.userLng) {
                            this.updateUserMarker(this.userLat, this.userLng);
                        }
                        this.updateMapBounds();
                    }

                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 300);
                },

                renderOfficeMarker(lat, lng, radius) {
                    if (!this.map || typeof L === 'undefined') return;

                    const outletIcon = L.divIcon({
                        className: 'custom-outlet-marker',
                        html: `
                            <div style="position: relative; width: 32px; height: 42px; transform: translate(-50%, -100%);">
                                <svg width="32" height="42" viewBox="0 0 24 32" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));">
                                    <path d="M12 0C5.37258 0 0 5.37258 0 12C0 21 12 32 12 32C12 32 24 21 24 12C24 5.37258 18.6274 0 12 0Z" fill="#0B3B3C"/>
                                    <circle cx="12" cy="11" r="4.5" fill="#ffffff"/>
                                    <circle cx="12" cy="11" r="2.5" fill="#0B3B3C"/>
                                </svg>
                            </div>
                        `,
                        iconSize: [32, 42],
                        iconAnchor: [16, 42]
                    });

                    const popupHtml = '<strong>' + (this.outletInfo.name || 'Lokasi Kerja') + '</strong><br>' + (this.outletInfo.address || '');

                    if (this.markers.outlet) {
                        this.markers.outlet.setLatLng([lat, lng]);
                        this.markers.outlet.setPopupContent(popupHtml);
                    } else {
                        this.markers.outlet = L.marker([lat, lng], { icon: outletIcon })
                            .addTo(this.map)
                            .bindPopup(popupHtml);
                    }

                    if (this.markers.radius) {
                        this.markers.radius.setLatLng([lat, lng]);
                        this.markers.radius.setRadius(radius);
                    } else {
                        this.markers.radius = L.circle([lat, lng], {
                            radius: radius,
                            color: '#10b981',
                            fillColor: '#10b981',
                            fillOpacity: 0.12,
                            weight: 2,
                            dashArray: '5, 5'
                        }).addTo(this.map);
                    }
                },

                updateUserMarker(lat, lng) {
                    if (!this.map || typeof L === 'undefined' || !lat || !lng) return;

                    const dotColor = '#059669';
                    const pingColor = 'rgba(16, 185, 129, 0.35)';

                    const userIcon = L.divIcon({
                        className: 'custom-user-marker',
                        html: `
                            <div style="position: relative; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; transform: translate(-50%, -50%);">
                                <div style="position: absolute; width: 26px; height: 26px; border-radius: 9999px; background-color: ${pingColor}; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
                                <div style="width: 12px; height: 12px; border-radius: 9999px; background-color: ${dotColor}; border: 2.5px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>
                            </div>
                        `,
                        iconSize: [26, 26],
                        iconAnchor: [13, 13]
                    });

                    if (this.markers.user) {
                        this.markers.user.setLatLng([lat, lng]);
                        this.markers.user.setIcon(userIcon);
                    } else {
                        this.markers.user = L.marker([lat, lng], { icon: userIcon }).addTo(this.map);
                    }

                    this.updateMapBounds();
                },

                updateMapBounds() {
                    if (!this.map || typeof L === 'undefined') return;
                    if (this.userLat && this.userLng && this.officeLatitude !== null && this.officeLongitude !== null && !isNaN(parseFloat(this.officeLatitude))) {
                        const bounds = L.latLngBounds([
                            [parseFloat(this.officeLatitude), parseFloat(this.officeLongitude)],
                            [parseFloat(this.userLat), parseFloat(this.userLng)]
                        ]);
                        this.map.fitBounds(bounds, { padding: [45, 45], maxZoom: 18 });
                    } else if (this.officeLatitude !== null && this.officeLongitude !== null && !isNaN(parseFloat(this.officeLatitude))) {
                        this.map.setView([parseFloat(this.officeLatitude), parseFloat(this.officeLongitude)], 17);
                    }
                },

                calculateDistance(lat1, lon1, lat2, lon2) {
                    if (lat1 === null || lon1 === null || lat2 === null || lon2 === null || isNaN(lat1) || isNaN(lat2)) {
                        return null;
                    }
                    const R = 6371000;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a =
                        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return Math.round(R * c);
                },


                openRegularizationModal(row) {
                    this.regularizationError = '';
                    this.regularizationSuccess = '';
                    this.regularizationForm.shift_assignment_id = row.id;
                    this.regularizationForm.shift_title = row.shift;
                    this.regularizationForm.shift_date = row.date;
                    this.regularizationForm.shift_end_datetime = row.shift_end_datetime || '';
                    this.regularizationForm.shift_end_formatted = row.shift_end_formatted || '';
                    this.regularizationForm.requested_clock_out_time = row.shift_end_datetime || '';
                    this.regularizationForm.reason = '';
                    this.regularizationModalOpen = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },

                submitRegularization() {
                    this.regularizationError = '';
                    this.regularizationSuccess = '';

                    if (!this.regularizationForm.requested_clock_out_time) {
                        this.regularizationError = 'Jam pulang riil wajib diisi.';
                        return;
                    }
                    if (!this.regularizationForm.reason || this.regularizationForm.reason.trim().length < 10) {
                        this.regularizationError = 'Alasan koreksi minimal 10 karakter.';
                        return;
                    }

                    this.submittingRegularization = true;

                    fetch('{{ route('employee.attendance.regularization.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            shift_assignment_id: this.regularizationForm.shift_assignment_id,
                            requested_clock_out_time: this.regularizationForm.requested_clock_out_time,
                            reason: this.regularizationForm.reason
                        })
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            const errorMsg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Terjadi kesalahan saat mengajukan koreksi.');
                            throw new Error(errorMsg);
                        }
                        return data;
                    })
                    .then(data => {
                        this.submittingRegularization = false;
                        this.regularizationSuccess = 'Pengajuan koreksi kehadiran berhasil dikirim ke manajer.';
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    })
                    .catch(err => {
                        this.submittingRegularization = false;
                        this.regularizationError = err.message;
                    });
                }
            };
        };

        window.attendanceConfig = {
            initialMode: '{{ $attendanceState }}',
            clockInTime: '{{ $isClockedIn ? $todayShift->clock_in_time->toIso8601String() : '' }}',
            clockOutTime: '{{ $isClockedOut ? $todayShift->clock_out_time->toIso8601String() : '' }}',
            isActualClockedIn: {{ $isClockedIn ? 'true' : 'false' }},
            isActualClockedOut: {{ $isClockedOut ? 'true' : 'false' }},
            shiftEnded: {{ $shiftEnded ? 'true' : 'false' }},
            isLocationConfigured: {{ $isLocationConfigured ? 'true' : 'false' }},
            officeLatitude: {{ $officeLat !== null ? (float) $officeLat : 'null' }},
            officeLongitude: {{ $officeLng !== null ? (float) $officeLng : 'null' }},
            attendanceRadius: {{ (int) $radius }},
            outlet: @json($outlet),
            targetShiftSeconds: {{ (int) $targetShiftSeconds }},
            targetShiftHours: {{ (float) $targetShiftHours }},
            allowMockGps: {{ $allowMockGps ? 'true' : 'false' }}
        };
    </script>

    <div x-data="attendancePage(window.attendanceConfig)" class="space-y-5 pb-12">

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-xl border border-emerald-200 shadow-card flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span class="text-xs font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 text-rose-800 p-3.5 rounded-xl border border-rose-200 shadow-card flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span class="text-xs font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- 1. HEADER & TOP SUMMARY METRICS (HIGH DENSITY)                            --}}
        {{-- ========================================================================= --}}
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            {{-- Breadcrumb & Title --}}
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-1">
                    <span class="hover:text-slate-700">Karyawan</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-brand-primary font-semibold">Pencatatan Kehadiran</span>
                </nav>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight" data-testid="page-heading">
                    Pencatatan Kehadiran
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Terminal presensi mandiri karyawan terverifikasi koordinat outlet
                </p>
            </div>

            {{-- Compact High-Density Metrics Bar --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                <div class="px-3 py-2 rounded-xl bg-white border border-slate-200/80 shadow-card flex items-center gap-2 shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kehadiran</span>
                    <span class="text-xs font-extrabold text-slate-900 font-mono">{{ $stats['attendance_percentage'] }}%</span>
                    <span class="text-[10px] text-slate-400">({{ $stats['present_shifts'] }}/{{ $stats['total_shifts'] }})</span>
                </div>

                <div class="px-3 py-2 rounded-xl bg-white border border-slate-200/80 shadow-card flex items-center gap-2 shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tepat Waktu</span>
                    <span class="text-xs font-extrabold text-emerald-600 font-mono">{{ $stats['on_time_days'] }} Hari</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200/70">{{ $stats['on_time_status'] }}</span>
                </div>

                <div class="px-3 py-2 rounded-xl bg-white border border-slate-200/80 shadow-card flex items-center gap-2 shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Jam</span>
                    <span class="text-xs font-extrabold text-slate-900 font-mono">{{ $stats['total_work_hours'] }} Jam</span>
                    <span class="text-[10px] text-slate-400">Bulan Ini</span>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 2. MAIN LAYOUT (GRID KIRI & KANAN)                                        --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- --------------------------------------------------------------------- --}}
            {{-- BAGIAN KIRI: UTAMA - PRESENSI HARI INI (8 KOLOM)                      --}}
            {{-- --------------------------------------------------------------------- --}}
            <div class="lg:col-span-8 space-y-4">
                {{-- Card Utama --}}
                <div id="live-lock" data-testid="live-lock" class="bg-white rounded-xl border border-slate-200/80 p-5 md:p-6 shadow-card space-y-4">
                    
                    {{-- Header Card & Status Badge (State dari Backend, tidak bisa diubah manual) --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-1.5 h-5 rounded-full shrink-0
                                {{ $attendanceState === 'working' ? 'bg-teal-600' : ($attendanceState === 'completed' ? 'bg-emerald-600' : ($attendanceState === 'idle' ? 'bg-amber-500' : 'bg-slate-400')) }}"></div>
                            <div>
                                <h2 class="text-sm md:text-base font-bold text-slate-900 leading-tight" data-testid="today-shift-heading">Shift Hari Ini</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Validasi kehadiran otomatis via titik presensi outlet</p>
                            </div>
                        </div>

                        {{-- Status Pill (Read-Only, dikontrol backend) --}}
                        @if ($attendanceState === 'working')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-teal-50 text-teal-800 border border-teal-200 text-xs font-semibold self-start sm:self-auto">
                                <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                Sedang Bekerja
                            </span>
                        @elseif ($attendanceState === 'completed')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold self-start sm:self-auto">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-700"></i>
                                Shift Selesai
                            </span>
                        @elseif ($attendanceState === 'no_shift')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium self-start sm:self-auto">
                                <i data-lucide="calendar-x" class="w-3.5 h-3.5"></i>
                                Tidak Ada Shift Terjadwal
                            </span>
                        @elseif ($shiftEnded)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold self-start sm:self-auto">
                                <i data-lucide="clock-x" class="w-3.5 h-3.5"></i>
                                Shift Telah Berakhir
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold self-start sm:self-auto">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Belum Masuk
                            </span>
                        @endif
                    </div>

                    {{-- Informasi Pengguna & Shift --}}
                    <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3.5 grid grid-cols-1 sm:grid-cols-3 gap-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/60">
                        {{-- Karyawan --}}
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-card">
                                {{ $initials }}
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Karyawan</span>
                                <span class="text-xs font-bold text-slate-900 block leading-tight truncate">{{ $userName }}</span>
                                <span class="text-[11px] text-slate-500 block truncate">{{ $userRole }} ({{ $userCode }})</span>
                            </div>
                        </div>

                        {{-- Penugasan Outlet --}}
                        <div class="flex items-start sm:pl-3 pt-2.5 sm:pt-0 gap-2.5">
                            <div class="p-1.5 rounded-lg bg-teal-50 text-teal-700 border border-teal-200/70 shrink-0 mt-0.5">
                                <i data-lucide="store" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Outlet</span>
                                <span class="text-xs font-bold text-slate-900 block leading-tight truncate" x-text="outletInfo.name">{{ $outletName }}</span>
                                <span class="text-[11px] text-slate-500 block truncate" x-text="outletInfo.address">{{ $outletAddress }}</span>
                            </div>
                        </div>

                        {{-- Jadwal Shift --}}
                        <div class="flex items-start sm:pl-3 pt-2.5 sm:pt-0 gap-2.5">
                            <div class="p-1.5 rounded-lg bg-indigo-50 text-brand-primary border border-indigo-200/70 shrink-0 mt-0.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Jadwal Shift</span>
                                <span class="text-xs font-bold text-slate-900 block leading-tight truncate">
                                    {{ $shiftTitle }} ({{ $shiftStart }} – {{ $shiftEnd }})
                                </span>
                                <span class="text-[11px] font-semibold text-brand-primary block">Rencana: 8 Jam Kerja</span>
                            </div>
                        </div>
                    </div>

                    {{-- Banner Peringatan Jika Lokasi Kerja Belum Dikonfigurasi --}}
                    <div x-show="!isLocationConfigured" class="bg-amber-50 border border-amber-300 rounded-xl px-3.5 py-2.5 flex items-center gap-2.5 text-xs font-semibold text-amber-900">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                        <span>Lokasi kerja belum diatur oleh admin di Pengaturan Platform. Tombol Clock-In dinonaktifkan.</span>
                    </div>

                    {{-- Banner Notifikasi Titik Presensi Outlet Dinamis --}}
                    <div x-show="isLocationConfigured"
                         :class="isInsideRadius ? 'bg-emerald-50/60 border-emerald-200/80' : 'bg-rose-50/60 border-rose-200/80'"
                         class="border rounded-xl px-3.5 py-2.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 transition-colors">
                        <div class="flex items-center gap-2">
                            <span :class="isInsideRadius ? 'bg-emerald-500' : 'bg-rose-500'"
                                  class="w-2 h-2 rounded-full shrink-0"></span>
                            <p class="text-xs text-slate-800">
                                <strong :class="isInsideRadius ? 'text-emerald-950' : 'text-rose-950'"
                                        class="font-bold"
                                        x-text="isInsideRadius ? 'Radius Outlet Terverifikasi:' : 'Di Luar Radius Outlet:'">
                                    Radius Outlet Terverifikasi:
                                </strong> 
                                <span x-text="radiusNotificationText">Menghubungkan ke titik presensi outlet...</span> 
                                <span class="text-slate-500" x-text="'(Radius aman < ' + outletInfo.radius + 'm)'">(Radius aman &lt; {{ $outletInfo['radius'] ?? 50 }}m)</span>
                            </p>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-teal-50 border border-teal-200 text-teal-800 text-[11px] font-semibold shrink-0 self-start sm:self-auto">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-teal-700"></i>
                            <span x-text="verificationStatusText">Lokasi Presensi Terverifikasi</span>
                        </div>
                    </div>

                    {{-- Status Shift Saat Ini (dikontrol backend, bukan Alpine) --}}
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @if ($attendanceState === 'working')
                            @php $isTodayLate = $todayLateMinutes > 0; @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-teal-50 text-teal-800 border border-teal-200 font-semibold">
                                <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                Sedang Bekerja
                            </span>
                            <span class="text-slate-400 font-medium">•</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md {{ $isTodayLate ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }} font-medium">
                                <i data-lucide="{{ $isTodayLate ? 'alert-circle' : 'check-circle' }}" class="w-3.5 h-3.5 {{ $isTodayLate ? 'text-rose-600' : 'text-emerald-600' }}"></i>
                                Masuk: {{ $clockInFormatted }}
                                ({{ $isTodayLate ? 'Terlambat ' . $todayLateFormatted : 'Hadir Tepat Waktu' }})
                            </span>

                        @elseif ($attendanceState === 'completed')
                            @php $isTodayLate = $todayLateMinutes > 0; @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-700"></i>
                                Selesai Bekerja
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md {{ $isTodayLate ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }} font-medium">
                                <i data-lucide="log-in" class="w-3 h-3"></i>
                                Masuk: {{ $clockInFormatted }}
                                ({{ $isTodayLate ? 'Terlambat ' . $todayLateFormatted : 'Tepat Waktu' }})
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 text-slate-700 border border-slate-200 font-medium">
                                <i data-lucide="log-out" class="w-3 h-3"></i>
                                Pulang: {{ $clockOutFormatted }}
                            </span>

                        @elseif ($shiftEnded && $attendanceState === 'idle')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-rose-50 text-rose-800 border border-rose-200 font-semibold">
                                <i data-lucide="clock-x" class="w-3.5 h-3.5"></i>
                                Shift Berakhir Tanpa Presensi
                            </span>
                            <span class="text-slate-400 font-medium">• Shift berakhir {{ $shiftEnd }} {{ $tzAbbr }}</span>

                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 border border-amber-200 font-semibold">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Belum Masuk
                            </span>
                            <span class="text-slate-400 font-medium">• Menunggu Clock In</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 border border-slate-200 font-medium">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-500"></i>
                                Jadwal Masuk: {{ $shiftStart }} {{ $tzAbbr }}
                            </span>
                        @endif
                    </div>

                    {{-- Timer & Progress Area (Clean Modern SaaS Punch Station) --}}
                    <div id="live-clock" data-testid="live-clock" class="bg-slate-900 text-white rounded-xl p-5 border border-slate-800 shadow-xs">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                            
                            {{-- Timer Box Kiri --}}
                            <div class="md:col-span-7">
                                @if ($attendanceState === 'completed')
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">
                                        TOTAL DURASI BEKERJA
                                    </span>
                                    @php
                                        $totalMin = $isClockedIn && $isClockedOut
                                            ? \Carbon\Carbon::parse($todayShift->clock_in_time)->diffInMinutes($todayShift->clock_out_time)
                                             : 0;
                                        $totalH = floor($totalMin / 60);
                                        $totalM = $totalMin % 60;
                                        $totalDurStr = $totalH . ' Jam ' . str_pad($totalM, 2, '0', STR_PAD_LEFT) . ' Menit';
                                    @endphp
                                    <div class="flex items-baseline gap-2.5">
                                        <span class="text-3xl md:text-5xl font-mono font-bold text-emerald-400 tracking-tight">
                                            {{ str_pad($totalH, 2, '0', STR_PAD_LEFT) }} : {{ str_pad($totalM, 2, '0', STR_PAD_LEFT) }} : 00
                                        </span>
                                    </div>
                                    <p class="text-xs text-emerald-300 mt-2 font-medium">
                                        Shift selesai. Total bekerja: {{ $totalDurStr }}.
                                    </p>
                                @elseif ($attendanceState === 'working')
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                        DURASI BEKERJA BERJALAN
                                    </span>
                                    <div class="flex items-baseline gap-2.5">
                                        <span x-text="timerDisplay" class="text-3xl md:text-5xl font-mono font-bold text-emerald-400 tracking-tight">
                                            00 : 00 : 00
                                        </span>
                                        <span class="text-xs font-semibold text-slate-400">/ Target {{ $targetShiftFormatted }}</span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-2 font-medium">
                                        Telah menyelesaikan <strong class="font-bold text-white" x-text="progressPercent + '%'">0%</strong> dari target shift hari ini
                                    </p>
                                @else
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                        DURASI BEKERJA
                                    </span>
                                    <div class="flex items-baseline gap-2.5">
                                        <span class="text-3xl md:text-5xl font-mono font-bold text-slate-500 tracking-tight">
                                            00 : 00 : 00
                                        </span>
                                        <span class="text-xs font-semibold text-slate-500">/ Target {{ $targetShiftFormatted }}</span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-2 font-medium">
                                        @if ($shiftEnded && $attendanceState === 'idle')
                                            Shift telah berakhir. Presensi tidak dapat dilakukan.
                                        @else
                                            Shift belum dimulai. Lakukan Clock-In saat tiba di lokasi outlet.
                                        @endif
                                    </p>
                                @endif
                            </div>

                            {{-- Progress Bar Kanan --}}
                            <div class="md:col-span-5 bg-slate-800/80 p-3.5 rounded-lg border border-slate-700/80">
                                <div class="flex items-center justify-between text-xs font-semibold mb-2">
                                    <span class="text-slate-300">Progres Shift</span>
                                    <span class="text-emerald-400 font-bold font-mono" x-text="progressHoursText">0.0 / {{ number_format($targetShiftHours, 1) }} Jam</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" 
                                         :style="'width: ' + progressPercent + '%;'"></div>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-slate-400 font-mono mt-1.5">
                                    <span>{{ $shiftStart }} {{ $tzAbbr }}</span>
                                    <span>{{ $shiftEnd }} {{ $tzAbbr }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Button & Validation Banner Bawah --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2 border-t border-slate-100">
                        {{-- Validasi Kehadiran Sistem --}}
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg
                                {{ $attendanceState === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($attendanceState === 'working' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-slate-100 text-slate-600 border border-slate-200') }}
                                flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800 leading-tight">
                                    @if ($attendanceState === 'completed')
                                        Presensi Hari Ini Selesai
                                    @elseif ($attendanceState === 'working')
                                        Validasi Presensi Masuk Lengkap
                                    @else
                                        Validasi Presensi Otomatis
                                    @endif
                                </p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    @if ($attendanceState === 'completed')
                                        Masuk {{ $clockInFormatted }} · Pulang {{ $clockOutFormatted }}
                                    @elseif ($attendanceState === 'working')
                                        Presensi masuk {{ $outletName }} tercatat pada {{ $clockInFormatted }}
                                    @else
                                        Pencatatan presensi terverifikasi dengan koordinat resmi lokasi kerja.
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Action Button — Mobile First Utility Button --}}
                        <div class="shrink-0 w-full sm:w-auto" id="attendance-action-container">
                            @if ($attendanceState === 'working')
                                {{-- Tombol Clock-Out (Mode 2: Sedang Bekerja) --}}
                                <form action="{{ route('employee.attendance.clock-out') }}" method="POST" id="form-clock-out"
                                      @submit="if (isPastShiftEnd && !lateClockOutConfirmed) { $event.preventDefault(); lateClockOutModalOpen = true; return false; }">
                                    @csrf
                                    <input type="hidden" name="notes" :value="lateClockOutNotes">
                                    <button type="submit" id="btn-clock-out" data-testid="btn-clock-out"
                                            :disabled="!isInsideRadius && isLocationConfigured"
                                            :class="(!isInsideRadius && isLocationConfigured) 
                                                ? 'opacity-50 cursor-not-allowed bg-slate-300 hover:bg-slate-300 active:bg-slate-300 shadow-none pointer-events-none' 
                                                : 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white'"
                                            class="w-full sm:w-auto whitespace-nowrap flex items-center justify-center gap-2 rounded-xl px-6 py-3 font-bold shadow-xs hover:shadow-sm transition-all text-xs tracking-wide">
                                        <i data-lucide="log-out" class="w-4 h-4 shrink-0"></i>
                                        <span class="whitespace-nowrap">Clock-Out</span>
                                    </button>
                                </form>

                            @elseif ($attendanceState === 'idle' && !$shiftEnded)
                                {{-- Tombol Clock-In (Mode 1: Belum Masuk, shift belum berakhir) --}}
                                <form action="{{ route('employee.attendance.clock-in') }}" method="POST" id="form-clock-in" @submit="if (!isInsideRadius || !isLocationConfigured) { $event.preventDefault(); return false; }">
                                    @csrf
                                    <input type="hidden" name="latitude" :value="userLat" value="{{ ($allowMockGps && $officeLatitude !== null) ? ((float)$officeLatitude + 0.00008) : '' }}">
                                    <input type="hidden" name="longitude" :value="userLng" value="{{ ($allowMockGps && $officeLongitude !== null) ? ((float)$officeLongitude + 0.00008) : '' }}">
                                    <button type="submit" id="btn-clock-in" data-testid="btn-clock-in"
                                            :disabled="!isInsideRadius || !isLocationConfigured"
                                            :class="(!isInsideRadius || !isLocationConfigured) 
                                                ? 'opacity-50 cursor-not-allowed bg-slate-300 hover:bg-slate-300 active:bg-slate-300 shadow-none pointer-events-none' 
                                                : 'bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white shadow-xs hover:shadow-sm'"
                                            :title="!isLocationConfigured ? 'Tombol nonaktif: Lokasi kerja belum diatur oleh admin' : (!isInsideRadius ? 'Tombol nonaktif: Anda berada di luar radius kantor (' + distanceText + ')' : 'Klik untuk mencatat kehadiran')"
                                            class="w-full sm:w-auto whitespace-nowrap flex items-center justify-center gap-2 rounded-xl px-6 py-3 font-bold transition-all text-xs tracking-wide">
                                        <i data-lucide="log-in" class="w-4 h-4 shrink-0"></i>
                                        <span class="whitespace-nowrap">Clock-In</span>
                                    </button>
                                </form>

                            @elseif ($attendanceState === 'idle' && $shiftEnded)
                                {{-- Tombol dinonaktifkan: shift sudah berakhir, tidak bisa clock-in --}}
                                <div class="w-full sm:w-auto whitespace-nowrap flex items-center justify-center gap-2 bg-slate-100 text-slate-400 rounded-xl px-6 py-3 font-semibold text-xs cursor-not-allowed select-none border border-slate-200">
                                    <i data-lucide="clock-x" class="w-4 h-4 shrink-0"></i>
                                    <span>Shift Telah Berakhir</span>
                                </div>

                            @elseif ($attendanceState === 'completed')
                                {{-- State Selesai: tidak ada tombol, tampilkan konfirmasi --}}
                                <div class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                                    <span>Presensi Selesai</span>
                                </div>

                            @else
                                {{-- Tidak ada shift hari ini --}}
                                <div class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 font-medium text-xs">
                                    <i data-lucide="calendar-x" class="w-4 h-4"></i>
                                    <span>Tidak Ada Shift</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Banner Catatan Supervisor (Di Bawah Card Utama) --}}
                <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-card flex items-start gap-2.5">
                    <div class="p-1 rounded bg-teal-50 text-teal-700 shrink-0 mt-0.5">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        <strong class="font-bold text-slate-900">Petunjuk Operasional Presensi:</strong> 
                        Pastikan Anda berada dalam radius presensi lokasi kerja <span x-text="outletInfo.name">{{ $outletName }}</span> (<span x-text="outletInfo.radius + 'm'">{{ $outletInfo['radius'] ?? 50 }}m</span>) dan memverifikasi kehadiran sebelum shift berakhir pukul {{ $shiftEnd }} {{ $tzAbbr }}.
                    </p>
                </div>
            </div>

            {{-- --------------------------------------------------------------------- --}}
            {{-- BAGIAN KANAN: WIDGET LOKASI & KETENTUAN (4 KOLOM)                     --}}
            {{-- --------------------------------------------------------------------- --}}
            <div class="lg:col-span-4 space-y-4">

                {{-- Widget 1: Lokasi Presensi Anda --}}
                <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-card space-y-3">
                    {{-- Header Widget --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs md:text-sm font-bold text-slate-900" data-testid="location-heading">Lokasi Presensi Anda</h3>
                        <span :class="isLocationConfigured ? 'text-emerald-600' : 'text-amber-600'"
                              class="inline-flex items-center gap-1.5 text-xs font-semibold">
                            <span :class="isLocationConfigured ? 'bg-emerald-500' : 'bg-amber-500'"
                                  class="w-1.5 h-1.5 rounded-full"></span>
                            <span x-text="connectionStatusText">Terhubung</span>
                        </span>
                    </div>

                    {{-- Banner Peringatan Jika Lokasi Belum Dikonfigurasi --}}
                    <div x-show="!isLocationConfigured" class="bg-amber-50 border border-amber-300 rounded-lg p-2.5 text-amber-900 text-xs font-medium flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Lokasi kerja belum diatur oleh admin di Pengaturan Platform. Presensi dinonaktifkan.</span>
                    </div>

                    {{-- Map View Container --}}
                    <div class="relative rounded-lg overflow-hidden border border-slate-200 h-[210px] bg-slate-100 select-none shadow-inner">
                        {{-- Leaflet Map Container --}}
                        <div id="attendance-map" class="w-full h-full z-0"></div>

                        {{-- Floating Card On Map --}}
                        <div class="absolute bottom-2 inset-x-2 z-[1000] bg-white/95 backdrop-blur-xs rounded-lg p-2 px-2.5 border border-slate-200 shadow-sm flex items-center justify-between gap-2 pointer-events-none sm:pointer-events-auto">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 truncate" x-text="outletInfo.name">{{ $outletName }}</p>
                                <p class="text-[10px] text-slate-500 truncate" x-text="outletInfo.address">{{ $outletAddress }}</p>
                            </div>
                            <span :class="isInsideRadius ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-rose-700 bg-rose-50 border-rose-200'" 
                                  class="text-[11px] font-bold border px-2 py-0.5 rounded-md whitespace-nowrap shadow-card transition-colors shrink-0"
                                  x-text="distanceText">
                                Mendeteksi...
                            </span>
                        </div>
                    </div>

                    {{-- 2 Kotak Mini Informasi Di Bawah Peta --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-slate-50 border border-slate-200/70 rounded-lg p-2.5 text-center">
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">BATAS MAKSIMAL</span>
                            <span class="text-xs font-bold text-slate-900 block mt-0.5" x-text="outletInfo.radius + ' Meter Radius'">{{ $outletInfo['radius'] ?? 50 }} Meter Radius</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-200/70 rounded-lg p-2.5 text-center">
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">STATUS AKURASI</span>
                            <span :class="isInsideRadius ? 'text-teal-700' : 'text-rose-700'" class="text-xs font-bold block mt-0.5" x-text="accuracyStatusText">Di Dalam Radius Aman</span>
                        </div>
                    </div>
                </div>

                {{-- Widget 2: Ketentuan Presensi Outlet --}}
                <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-card space-y-3">
                    {{-- Header Widget --}}
                    <div class="flex items-center gap-2">
                        <div class="p-1 rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="text-xs md:text-sm font-bold text-slate-900" data-testid="rules-heading">Ketentuan Presensi Outlet</h3>
                    </div>

                    {{-- List Nomor (1-4) --}}
                    <div class="space-y-2.5 text-xs text-slate-600">
                        {{-- 1. Radius Presensi --}}
                        <div class="flex items-start gap-2.5">
                            <span class="w-4 h-4 rounded bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                                1
                            </span>
                            <p class="leading-relaxed text-[11.5px]">
                                <strong class="font-bold text-slate-900">Radius Presensi:</strong> 
                                Maksimal <span x-text="outletInfo.radius + ' meter'">{{ $outletInfo['radius'] ?? 50 }} meter</span> dari titik koordinat resmi lokasi kerja.
                            </p>
                        </div>

                        {{-- 2. Jendela Clock-In --}}
                        <div class="flex items-start gap-2.5">
                            <span class="w-4 h-4 rounded bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                                2
                            </span>
                            <p class="leading-relaxed text-[11.5px]">
                                <strong class="font-bold text-slate-900">Jendela Masuk:</strong> 
                                Pencatatan masuk dapat dilakukan mulai H-15 menit sebelum jam kerja dimulai.
                            </p>
                        </div>

                        {{-- 3. Toleransi Keterlambatan --}}
                        <div class="flex items-start gap-2.5">
                            <span class="w-4 h-4 rounded bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                                3
                            </span>
                            <p class="leading-relaxed text-[11.5px]">
                                <strong class="font-bold text-slate-900">Toleransi Keterlambatan:</strong> 
                                Maksimal {{ $toleranceMin }} menit sebelum tercatat terlambat.
                            </p>
                        </div>

                        {{-- 4. Verifikasi Kehadiran --}}
                        <div class="flex items-start gap-2.5">
                            <span class="w-4 h-4 rounded bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                                4
                            </span>
                            <p class="leading-relaxed text-[11.5px]">
                                <strong class="font-bold text-slate-900">Verifikasi Kehadiran:</strong> 
                                Presensi diverifikasi berdasarkan penugasan outlet aktif dan waktu sistem resmi.
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Buka SOP --}}
                    <div class="pt-2 border-t border-slate-100">
                        <button type="button" 
                                @click="sopModalOpen = true"
                                class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition border border-slate-200">
                            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                            <span>Baca SOP Operasional Lengkap</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 3. TABEL RIWAYAT 7 HARI TERAKHIR (BAWAH)                                  --}}
        {{-- ========================================================================= --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="p-4 md:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-sm md:text-base font-bold text-slate-900" data-testid="history-heading">Riwayat Kehadiran</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rekap log jam masuk, pulang, durasi, dan status verifikasi 7 hari terakhir</p>
                </div>
                
                <div class="flex items-center gap-2">
                    {{-- Filter Dropdown --}}
                    <div class="relative">
                        <select x-model="filterStatus" 
                                class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2.5 py-1.5 pr-8 focus:ring-1 focus:ring-slate-900 font-medium">
                            <option value="all">Semua Status</option>
                            <option value="hadir">Hadir Tepat Waktu</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="off">Off / Libur</option>
                        </select>
                    </div>

                    {{-- Riwayat Koreksi --}}
                    <a href="{{ route('employee.attendance.regularization.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-brand-primary text-xs font-semibold transition border border-indigo-200/60">
                        <i data-lucide="file-clock" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Riwayat Koreksi</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-slate-700 font-bold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5">Tanggal &amp; Hari</th>
                            <th class="px-4 py-2.5">Shift Kerja</th>
                            <th class="px-4 py-2.5">Outlet</th>
                            <th class="px-4 py-2.5">Clock-In</th>
                            <th class="px-4 py-2.5">Clock-Out</th>
                            <th class="px-4 py-2.5">Total Durasi</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5 text-center">Koreksi Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($historyData as $row)
                            <tr x-show="filterStatus === 'all' || filterStatus === '{{ $row['status_key'] }}'" 
                                class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $row['date'] }}</div>
                                    <div class="text-[11px] text-slate-400 font-medium">{{ $row['day'] }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-800">
                                    {{ $row['shift'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $row['outlet'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-[11.5px] {{ $row['is_late'] ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                    {{ $row['clock_in'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-[11.5px] text-slate-700">
                                    <div class="flex flex-col gap-1">
                                        <span>{{ $row['clock_out'] }}</span>
                                        @if ($row['is_auto_clockout'])
                                            <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-semibold px-1.5 py-0.2 rounded w-max">
                                                <i data-lucide="clock-alert" class="w-3 h-3 text-amber-700"></i> Auto Clock-Out
                                            </span>
                                        @elseif ($row['overtime_status'] === 'pending_approval')
                                            <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-[10px] font-semibold px-1.5 py-0.2 rounded w-max border border-amber-200">
                                                <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i> Lembur Pending
                                            </span>
                                        @elseif ($row['overtime_status'] === 'approved')
                                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-semibold px-1.5 py-0.2 rounded w-max border border-emerald-200">
                                                <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i> Lembur Disetujui
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-900">
                                    {{ $row['duration'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($row['badge_theme'] === 'emerald')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $row['status_label'] }}
                                        </span>
                                    @elseif ($row['badge_theme'] === 'rose')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            {{ $row['status_label'] }}
                                        </span>
                                    @elseif ($row['badge_theme'] === 'indigo')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            {{ $row['status_label'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                            {{ $row['status_label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if ($row['regularization_status'] === 'pending')
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-semibold px-2 py-0.5 rounded">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                            Koreksi: Pending
                                        </span>
                                    @elseif ($row['regularization_status'] === 'approved')
                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold px-2 py-0.5 rounded">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            Koreksi: Disetujui
                                        </span>
                                    @elseif ($row['regularization_status'] === 'rejected')
                                        <div class="inline-flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 border border-rose-200 text-xs font-semibold px-2 py-0.5 rounded">
                                                <i data-lucide="x-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                                                Koreksi: Ditolak
                                            </span>
                                            <button type="button" 
                                                    @click="openRegularizationModal({{ json_encode($row) }})" 
                                                    class="text-xs text-brand-primary hover:underline font-medium inline-flex items-center gap-1">
                                                <i data-lucide="file-pen-line" class="w-3.5 h-3.5"></i>
                                                Ajukan Ulang
                                            </button>
                                        </div>
                                    @elseif ($row['is_auto_clockout'] || $row['can_regularize'])
                                        <button type="button" 
                                                @click="openRegularizationModal({{ json_encode($row) }})" 
                                                class="text-xs text-brand-primary hover:underline font-medium inline-flex items-center gap-1">
                                            <i data-lucide="file-pen-line" class="w-3.5 h-3.5"></i>
                                            Ajukan Koreksi Jam
                                        </button>
                                    @else
                                        <span class="text-slate-400 font-mono text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="calendar-x" class="w-6 h-6 text-slate-300"></i>
                                        <p class="text-xs font-medium text-slate-600">Belum ada riwayat kehadiran dalam 7 hari terakhir.</p>
                                        <p class="text-[11px] text-slate-400">Presensi Anda akan otomatis tercatat di sini setelah melakukan Clock-In.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Tabel --}}
            <div class="p-3.5 px-5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Hadir Tepat Waktu
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Terlambat
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        Off / Libur
                    </span>
                </div>
                <div class="text-slate-400 font-medium text-[11px]">
                    Menampilkan {{ count($historyData) }} entri riwayat presensi
                </div>
            </div>
        </div>
        {{-- ========================================================================= --}}
        {{-- MODAL SOP OPERASIONAL LENGKAP                                             --}}
        {{-- ========================================================================= --}}
        <div x-show="sopModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true"
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="sopModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="sopModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="sopModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    
                    <div class="p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-teal-50 text-teal-700">
                                    <i data-lucide="book-open" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900" id="modal-title">SOP Presensi Lokasi Kerja <span x-text="outletInfo.name">{{ $outletName }}</span></h3>
                                    <p class="text-[11px] text-slate-500">Standar Prosedur Operasional Kehadiran Karyawan</p>
                                </div>
                            </div>
                            <button type="button" @click="sopModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-100 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="space-y-3 text-xs text-slate-600 leading-relaxed max-h-[60vh] overflow-y-auto pr-1">
                            <div>
                                <h4 class="font-bold text-slate-900 text-xs mb-0.5">1. Ketentuan Radius Presensi</h4>
                                <p>Setiap presensi tercatat otomatis dalam radius aman <span x-text="outletInfo.radius + ' meter'">{{ $outletInfo['radius'] ?? 50 }} meter</span> dari titik resmi lokasi kerja. Pastikan lokasi kerja telah diatur oleh admin di Pengaturan Platform.</p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-xs mb-0.5">2. Waktu Clock-In &amp; Toleransi</h4>
                                <p>Clock-In dapat dilakukan mulai 15 menit sebelum jam mulai shift hingga maksimal {{ $toleranceMin }} menit setelah jam mulai shift (masa toleransi). Keterlambatan lebih dari {{ $toleranceMin }} menit akan tercatat sebagai status Terlambat.</p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-xs mb-0.5">3. Verifikasi Kehadiran Karyawan</h4>
                                <p>Presensi dicatat berdasarkan waktu sistem dan titik penugasan outlet yang sah.</p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-xs mb-0.5">4. Clock-Out &amp; Overtime</h4>
                                <p>Clock-Out dilakukan setelah menyelesaikan seluruh tanggung jawab operasional dan serah terima shift (handover). Lembur memerlukan persetujuan Store Manager.</p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <button type="button" @click="sopModalOpen = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold">
                                Saya Mengerti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL FORM PENGAJUAN KOREKSI KEHADIRAN (REGULARISASI)                     --}}
        {{-- ========================================================================= --}}
        <div x-show="regularizationModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="regularization-modal-title" role="dialog" aria-modal="true"
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="regularizationModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="if (!submittingRegularization) regularizationModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="regularizationModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                    
                    <div class="p-5 sm:p-6 space-y-4">
                        {{-- Modal Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-indigo-50 text-brand-primary">
                                    <i data-lucide="file-pen-line" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900" id="regularization-modal-title">Ajukan Koreksi Kehadiran</h3>
                                    <p class="text-[11px] text-slate-500">Penyesuaian jam pulang riil akibat Auto Clock-Out</p>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="regularizationModalOpen = false" 
                                    :disabled="submittingRegularization"
                                    class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-100 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        {{-- Shift Info Pill --}}
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80 space-y-1.5 text-xs">
                            <div class="flex justify-between items-center text-slate-500 font-medium">
                                <span>Shift Kerja:</span>
                                <span class="font-bold text-slate-900" x-text="regularizationForm.shift_title"></span>
                            </div>
                            <div class="flex justify-between items-center text-slate-500 font-medium">
                                <span>Tanggal Shift:</span>
                                <span class="font-semibold text-slate-800" x-text="regularizationForm.shift_date"></span>
                            </div>
                            <div class="flex justify-between items-center text-slate-500 font-medium">
                                <span>Jam Selesai Resmi:</span>
                                <span class="font-mono font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200" x-text="regularizationForm.shift_end_formatted"></span>
                            </div>
                        </div>

                        {{-- Feedback Alerts --}}
                        <template x-if="regularizationError">
                            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-2 text-xs">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                                <span x-text="regularizationError"></span>
                            </div>
                        </template>

                        <template x-if="regularizationSuccess">
                            <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-2 text-xs">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                <span x-text="regularizationSuccess"></span>
                            </div>
                        </template>

                        {{-- Form Inputs --}}
                        <form @submit.prevent="submitRegularization()" class="space-y-3.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Jam Pulang Riil <span class="text-rose-500">*</span>
                                </label>
                                <input type="datetime-local" 
                                       x-model="regularizationForm.requested_clock_out_time"
                                       :disabled="submittingRegularization"
                                       required
                                       class="w-full text-xs font-mono rounded-lg border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 shadow-card">
                                <p class="text-[10px] text-slate-400 mt-1">
                                    Wajib melewati jam selesai shift resmi dan berada dalam kurun 24 jam.
                                </p>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Alasan Koreksi <span class="text-rose-500">*</span>
                                </label>
                                <textarea x-model="regularizationForm.reason"
                                          :disabled="submittingRegularization"
                                          rows="3"
                                          required
                                          minlength="10"
                                          placeholder="Tuliskan alasan mengapa Anda belum sempat clock-out atau rincian pekerjaan lembur yang dilakukan (minimal 10 karakter)..."
                                          class="w-full text-xs rounded-lg border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 shadow-card"></textarea>
                                <p class="text-[10px] text-slate-400 mt-1">
                                    Minimal 10 karakter. Alasan ini akan diverifikasi oleh manajer Anda.
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                                <button type="button" 
                                        @click="regularizationModalOpen = false" 
                                        :disabled="submittingRegularization"
                                        class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                    Batal
                                </button>
                                <button type="submit" 
                                        :disabled="submittingRegularization"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 disabled:opacity-75 text-white rounded-lg text-xs font-semibold shadow-card transition-all">
                                    <template x-if="submittingRegularization">
                                        <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                                    </template>
                                    <template x-if="!submittingRegularization">
                                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                    </template>
                                    <span x-text="submittingRegularization ? 'Mengirim...' : 'Kirim Pengajuan'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL KONFIRMASI CLOCK-OUT LEWAT JAM SHIFT (KOREKSI / LEMBUR)            --}}
        {{-- ========================================================================= --}}
        <div x-show="lateClockOutModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog" aria-modal="true"
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="lateClockOutModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="lateClockOutModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="lateClockOutModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                    
                    <div class="p-5 sm:p-6 space-y-4">
                        {{-- Modal Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Clock-Out Lewat Jam Shift</h3>
                                    <p class="text-[11px] text-slate-500">Konfirmasi pengajuan koreksi / lembur</p>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="lateClockOutModalOpen = false" 
                                    class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-100 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="p-3 bg-amber-50/70 rounded-lg border border-amber-200/80 text-xs text-amber-900 space-y-1.5">
                            <p class="font-medium">
                                Waktu Clock-Out Anda telah melewati jam selesai jadwal shift resmi.
                            </p>
                            <p class="text-[11px] text-amber-800/80">
                                Jam reguler shift akan tercatat hingga batas akhir shift. Kelebihan waktu akan otomatis diajukan ke manajer sebagai permohonan koreksi jam/lembur.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Keterangan / Catatan Pekerjaan <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <textarea x-model="lateClockOutNotes" 
                                      rows="3" 
                                      class="w-full text-xs rounded-lg border border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 p-2.5 transition"
                                      placeholder="Contoh: Menyelesaikan closing kasir / melayani pesanan terakhir..."></textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button type="button" 
                                    @click="lateClockOutModalOpen = false" 
                                    class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                Batal
                            </button>
                            <button type="button" 
                                    @click="lateClockOutConfirmed = true; $nextTick(() => document.getElementById('form-clock-out').submit());"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-card transition-all">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                <span>Konfirmasi Clock-Out</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Script untuk anti-double submit form & update icon --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Anti-double submit untuk Clock In
            const formClockIn = document.getElementById('form-clock-in');
            if (formClockIn) {
                formClockIn.addEventListener('submit', function() {
                    const btn = document.getElementById('btn-clock-in');
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-75', 'cursor-not-allowed');
                        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>MEMPROSES...</span>';
                        if (window.lucide) window.lucide.createIcons();
                    }
                });
            }

            // Anti-double submit untuk Clock Out
            const formClockOut = document.getElementById('form-clock-out');
            if (formClockOut) {
                formClockOut.addEventListener('submit', function(e) {
                    if (e.defaultPrevented) return;
                    const btn = document.getElementById('btn-clock-out');
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-75', 'cursor-not-allowed');
                        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>MEMPROSES...</span>';
                        if (window.lucide) window.lucide.createIcons();
                    }
                });
            }

            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</x-app-layout>
