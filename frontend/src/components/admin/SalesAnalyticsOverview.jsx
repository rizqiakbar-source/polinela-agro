import React, { useState, useRef, useCallback } from 'react';
import { formatRupiah } from '../../utils/format';
import Swal from 'sweetalert2';
import { 
    DollarSign, 
    ShoppingBag, 
    Target, 
    Star, 
    AlertTriangle, 
    Sparkles, 
    Package,
    ArrowUpRight,
    CheckCircle2,
    Calendar,
    ChevronRight,
    ExternalLink,
    SlidersHorizontal,
    MoveHorizontal
} from 'lucide-react';

const SalesAnalyticsOverview = ({ 
    stats = {}, 
    sparklines = {}, 
    goals = {}, 
    dailyAnalytics = [], 
    monthlyChart = {}, 
    productHeatmap = [], 
    recentReviews = [], 
    lowStockProducts = [],
    unitStats = [],
    roleTitle = 'Store Overview',
    roleSubtitle = "Here's how your store is performing today",
    onQuickStock = null
}) => {
    const [viewMode, setViewMode] = useState('daily'); // 'daily' | 'monthly'
    const [hoveredPoint, setHoveredPoint] = useState(null);
    const [selectedPointIndex, setSelectedPointIndex] = useState(null);
    const [isDragging, setIsDragging] = useState(false);
    const chartSvgRef = useRef(null);

    // Extract exact numbers from database (never use fake dummy fallbacks)
    const totalRevenue = Number(stats?.total_pendapatan ?? stats?.total_omset ?? 0);
    const totalOrders = Number(stats?.total_transaksi ?? stats?.total_pesanan ?? 0);

    // Prepare chart data strictly based on real viewMode data
    let chartPoints = [];
    if (viewMode === 'daily' && dailyAnalytics && dailyAnalytics.length > 0) {
        chartPoints = dailyAnalytics.map(d => ({
            label: d.label || d.date,
            value: Number(d.revenue || 0),
            sales: Number(d.sales_count || 0)
        }));
    } else if (viewMode === 'monthly' && monthlyChart?.totals && monthlyChart.totals.length > 0) {
        chartPoints = monthlyChart.totals.map((tot, idx) => ({
            label: monthlyChart.labels?.[idx] || `Bln ${idx + 1}`,
            value: Number(tot || 0),
            sales: Number(monthlyChart.counts?.[idx] || 0)
        }));
    } else {
        // Generate current 7 calendar days with 0 initial value if database is empty
        const daysOfWeek = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        const monthsNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        chartPoints = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            chartPoints.push({
                label: `${d.getDate()} ${monthsNames[d.getMonth()]}`,
                value: 0,
                sales: 0
            });
        }
    }

    const dataMax = Math.max(...chartPoints.map(p => p.value), 0);
    const maxValue = dataMax > 0 ? Math.ceil(dataMax * 1.25 / 10000) * 10000 : 100000;
    const chartHeight = 180;
    const chartWidth = 560;
    const paddingX = 40;
    const paddingY = 25;

    // Generate smooth SVG points
    const getSvgPoints = () => {
        const n = chartPoints.length;
        if (n === 0) return [];
        const stepX = (chartWidth - paddingX * 2) / (n - 1 || 1);
        return chartPoints.map((p, i) => {
            const x = paddingX + i * stepX;
            const ratio = maxValue > 0 ? (p.value / maxValue) : 0;
            const y = chartHeight - paddingY - ratio * (chartHeight - paddingY * 2);
            return { x, y, ...p, originalIndex: i };
        });
    };

    const svgPoints = getSvgPoints();

    // Generate Monotone Cubic Spline to avoid artificial overshooting
    const generateSplinePath = (pts) => {
        if (pts.length === 0) return '';
        if (pts.length === 1) return `M ${pts[0].x} ${pts[0].y}`;
        if (pts.length === 2) return `M ${pts[0].x},${pts[0].y} L ${pts[1].x},${pts[1].y}`;

        let path = `M ${pts[0].x.toFixed(2)},${pts[0].y.toFixed(2)}`;
        for (let i = 0; i < pts.length - 1; i++) {
            const p0 = pts[Math.max(0, i - 1)];
            const p1 = pts[i];
            const p2 = pts[i + 1];
            const p3 = pts[Math.min(pts.length - 1, i + 2)];

            // Catmull-Rom to Cubic Bezier conversion
            const cp1x = p1.x + (p2.x - p0.x) / 6;
            let cp1y = p1.y + (p2.y - p0.y) / 6;
            const cp2x = p2.x - (p3.x - p1.x) / 6;
            let cp2y = p2.y - (p3.y - p1.y) / 6;

            // Clamp vertical overshoot if flattening
            const minY = Math.min(p1.y, p2.y);
            const maxY = Math.max(p1.y, p2.y);
            if (p1.y === p2.y) {
                cp1y = p1.y;
                cp2y = p2.y;
            } else {
                cp1y = Math.max(minY, Math.min(maxY, cp1y));
                cp2y = Math.max(minY, Math.min(maxY, cp2y));
            }

            path += ` C ${cp1x.toFixed(2)},${cp1y.toFixed(2)} ${cp2x.toFixed(2)},${cp2y.toFixed(2)} ${p2.x.toFixed(2)},${p2.y.toFixed(2)}`;
        }
        return path;
    };

    const splineLine = generateSplinePath(svgPoints);
    const splineArea = svgPoints.length > 0 
        ? `${splineLine} L ${svgPoints[svgPoints.length - 1].x.toFixed(2)},${chartHeight - paddingY} L ${svgPoints[0].x.toFixed(2)},${chartHeight - paddingY} Z`
        : '';

    // Active tooltip point default to hovered, selected, or last item
    const activePointIndex = hoveredPoint !== null 
        ? hoveredPoint 
        : (selectedPointIndex !== null ? selectedPointIndex : (svgPoints.length > 0 ? svgPoints.length - 1 : null));

    const activePoint = activePointIndex !== null && svgPoints[activePointIndex] ? svgPoints[activePointIndex] : null;

    // Pointer Scrubbing Handler for Dragging across timeline
    const updatePointFromPointer = useCallback((clientX) => {
        if (!chartSvgRef.current || svgPoints.length === 0) return;
        const rect = chartSvgRef.current.getBoundingClientRect();
        const relativeX = clientX - rect.left;
        const clampedRelX = Math.max(0, Math.min(rect.width, relativeX));
        const svgX = (clampedRelX / rect.width) * chartWidth;

        let closestIdx = 0;
        let minDistance = Infinity;
        svgPoints.forEach((pt, idx) => {
            const dist = Math.abs(pt.x - svgX);
            if (dist < minDistance) {
                minDistance = dist;
                closestIdx = idx;
            }
        });
        setSelectedPointIndex(closestIdx);
        setHoveredPoint(closestIdx);
    }, [svgPoints]);

    const handlePointerDown = (e) => {
        setIsDragging(true);
        if (chartSvgRef.current) {
            chartSvgRef.current.setPointerCapture?.(e.pointerId);
        }
        updatePointFromPointer(e.clientX);
    };

    const handlePointerMove = (e) => {
        updatePointFromPointer(e.clientX);
    };

    const handlePointerUp = (e) => {
        setIsDragging(false);
        if (chartSvgRef.current && chartSvgRef.current.hasPointerCapture?.(e.pointerId)) {
            chartSvgRef.current.releasePointerCapture(e.pointerId);
        }
    };

    const handlePointClick = (pt, idx) => {
        setSelectedPointIndex(idx);
        setHoveredPoint(idx);
    };

    const handleShowDetailModal = (pt) => {
        if (!pt) return;
        const aov = pt.sales > 0 ? Math.round(pt.value / pt.sales) : 0;
        Swal.fire({
            title: `📊 Rincian Penjualan`,
            html: `
                <div class="text-start p-3 bg-light rounded-4 mb-2" style="font-size: 13.5px;">
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted">Periode Waktu:</span>
                        <strong class="text-dark">${pt.label} (${viewMode === 'daily' ? 'Harian' : 'Bulanan'})</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted">Total Omset:</span>
                        <strong class="text-success fs-6">${formatRupiah(pt.value)}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted">Volume Transaksi:</span>
                        <span class="badge bg-primary rounded-pill px-2.5 py-1">${pt.sales} Pesanan</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Rata-rata / Pesanan (AOV):</span>
                        <strong>${formatRupiah(aov)}</strong>
                    </div>
                </div>
                <small class="text-muted d-block text-start">Klik tombol di bawah untuk melihat rincian arsip pesanan pada modul laporan.</small>
            `,
            showCancelButton: true,
            confirmButtonText: 'Buka Laporan Penjualan →',
            cancelButtonText: 'Tutup',
            confirmButtonColor: '#15803d',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/admin/reports';
            }
        });
    };

    // Sparklines data for Top Cards (default to 7 zeroes if no data)
    const revSpark = sparklines?.revenue || [0, 0, 0, 0, 0, 0, 0];
    const maxRevSpark = Math.max(...revSpark, 1);
    const ordSpark = sparklines?.orders || [0, 0, 0, 0, 0, 0, 0];
    const maxOrdSpark = Math.max(...ordSpark, 1);

    // Goal Arc percentage & numbers
    const targetVal = Number(goals?.target ?? 10000000);
    const achievedVal = Number(goals?.achieved ?? totalRevenue);
    const goalPercent = targetVal > 0 ? Math.min(100, Math.round((achievedVal / targetVal) * 100)) : 0;

    // Semi circle arc calculation (radius 50)
    // Semicircle perimeter = pi * r = 3.14159 * 50 = 157.08
    const arcCircumference = 157.08;
    const strokeDashoffset = arcCircumference - (goalPercent / 100) * arcCircumference;

    return (
        <div className="analytics-dashboard mb-4">
            {/* Header */}
            <div className="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h4 className="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <span>{roleTitle}</span>
                    </h4>
                    <p className="text-muted small mb-0">{roleSubtitle}</p>
                </div>
            </div>

            {/* TOP 3 METRIC CARDS */}
            <div className="row g-3 mb-4">
                {/* 1. Total Revenue Card */}
                <div className="col-lg-4 col-md-6">
                    <div className="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden">
                        <div className="d-flex align-items-start justify-content-between">
                            <div>
                                <div className="d-flex align-items-center gap-2 mb-2">
                                    <div className="p-1.5 rounded-3 bg-primary bg-opacity-10 text-primary">
                                        <DollarSign size={16} />
                                    </div>
                                    <span className="text-muted small fw-semibold">Total Revenue</span>
                                </div>
                                <h3 className="fw-bold text-dark mb-2">
                                    {formatRupiah(totalRevenue)}
                                </h3>
                                <div className="d-flex align-items-center gap-1">
                                    <span className="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 rounded-pill small" style={{ fontSize: '11px' }}>
                                        {stats?.growth_revenue || '+0%'}
                                    </span>
                                    <span className="text-muted" style={{ fontSize: '11px' }}>dari periode lalu</span>
                                </div>
                            </div>

                            {/* Mini Sparkline Bar Chart */}
                            <div className="d-flex align-items-end gap-1 pt-3" style={{ height: '70px' }}>
                                {revSpark.map((v, i) => {
                                    const h = v > 0 ? Math.max(15, Math.round((v / maxRevSpark) * 100)) : 10;
                                    const isHighlighted = i === revSpark.length - 1 && v > 0;
                                    return (
                                        <div 
                                            key={i} 
                                            className="rounded-pill"
                                            style={{
                                                width: '6px',
                                                height: `${h}%`,
                                                background: isHighlighted ? '#1e40af' : '#e2e8f0',
                                                transition: 'all 0.2s ease'
                                            }}
                                            title={`Rp ${Number(v).toLocaleString()}`}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>

                {/* 2. Total Orders Card */}
                <div className="col-lg-4 col-md-6">
                    <div className="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden">
                        <div className="d-flex align-items-start justify-content-between">
                            <div>
                                <div className="d-flex align-items-center gap-2 mb-2">
                                    <div className="p-1.5 rounded-3 bg-primary bg-opacity-10 text-primary">
                                        <ShoppingBag size={16} />
                                    </div>
                                    <span className="text-muted small fw-semibold">Total Orders</span>
                                </div>
                                <h3 className="fw-bold text-dark mb-2">
                                    {totalOrders.toLocaleString()} <span className="text-muted fs-6 fw-normal">Pesanan</span>
                                </h3>
                                <div className="d-flex align-items-center gap-1">
                                    <span className="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 rounded-pill small" style={{ fontSize: '11px' }}>
                                        {stats?.growth_orders || '+0%'}
                                    </span>
                                    <span className="text-muted" style={{ fontSize: '11px' }}>dari periode lalu</span>
                                </div>
                            </div>

                            {/* Mini Sparkline Bar Chart */}
                            <div className="d-flex align-items-end gap-1 pt-3" style={{ height: '70px' }}>
                                {ordSpark.map((v, i) => {
                                    const h = v > 0 ? Math.max(15, Math.round((v / maxOrdSpark) * 100)) : 10;
                                    const isHighlighted = i === ordSpark.length - 1 && v > 0;
                                    return (
                                        <div 
                                            key={i} 
                                            className="rounded-pill"
                                            style={{
                                                width: '6px',
                                                height: `${h}%`,
                                                background: isHighlighted ? '#1e40af' : '#e2e8f0',
                                                transition: 'all 0.2s ease'
                                            }}
                                            title={`${v} pesanan`}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>

                {/* 3. Monthly Goals Arc Gauge Card */}
                <div className="col-lg-4 col-md-12">
                    <div className="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div className="d-flex align-items-center justify-content-between">
                            <div>
                                <div className="d-flex align-items-center gap-2 mb-2">
                                    <div className="p-1.5 rounded-3 bg-primary bg-opacity-10 text-primary">
                                        <Target size={16} />
                                    </div>
                                    <span className="text-muted small fw-semibold">Monthly Goals</span>
                                </div>
                                <div className="d-flex gap-3 mb-1">
                                    <div>
                                        <small className="text-muted d-block" style={{ fontSize: '10px' }}>Target</small>
                                        <span className="fw-bold text-dark small">{formatRupiah(targetVal)}</span>
                                    </div>
                                    <div>
                                        <small className="text-muted d-block" style={{ fontSize: '10px' }}>Tercapai</small>
                                        <span className="fw-bold text-success small">{formatRupiah(achievedVal)}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Semi Circle Radial Gauge */}
                            <div className="position-relative d-flex flex-column align-items-center justify-content-end" style={{ width: '130px', height: '80px' }}>
                                <svg width="120" height="70" viewBox="0 0 120 70">
                                    {/* Track */}
                                    <path
                                        d="M 10,65 A 50,50 0 0,1 110,65"
                                        fill="none"
                                        stroke="#e2e8f0"
                                        strokeWidth="12"
                                        strokeLinecap="round"
                                    />
                                    {/* Filled Arc */}
                                    <path
                                        d="M 10,65 A 50,50 0 0,1 110,65"
                                        fill="none"
                                        stroke="#2563eb"
                                        strokeWidth="12"
                                        strokeLinecap="round"
                                        strokeDasharray={arcCircumference}
                                        strokeDashoffset={strokeDashoffset}
                                        style={{ transition: 'stroke-dashoffset 0.8s ease' }}
                                    />
                                </svg>
                                <div className="position-absolute text-center" style={{ bottom: '2px' }}>
                                    <span className="h5 fw-bolder text-dark mb-0">{goalPercent}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* MIDDLE ROW: SALES ANALYTICS CURVE & TOP PRODUCTS MATRIX */}
            <div className="row g-3 mb-4">
                {/* 1. Sales Analytics Spline Area Chart */}
                <div className="col-lg-7">
                    <div className="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div className="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <div>
                                <h6 className="fw-bold text-dark mb-0">Sales Analytics</h6>
                                <small className="text-muted" style={{ fontSize: '11px' }}>Grafik dinamika omset dan kuantitas transaksi</small>
                            </div>
                            <div className="d-flex align-items-center gap-2">
                                <select 
                                    className="form-select form-select-sm border-light-subtle rounded-pill small py-1 px-3"
                                    value={viewMode}
                                    onChange={(e) => setViewMode(e.target.value)}
                                    style={{ fontSize: '12px', width: 'auto' }}
                                >
                                    <option value="daily">Daily Sales</option>
                                    <option value="monthly">Monthly Sales</option>
                                </select>
                                <span className="badge bg-light text-muted border rounded-pill py-1.5 px-2.5 small" style={{ fontSize: '11px' }}>
                                    📅 {chartPoints[0]?.label || '-'} - {chartPoints[chartPoints.length - 1]?.label || '-'}
                                </span>
                            </div>
                        </div>

                        {/* Interactive SVG Spline Area Chart with Drag & Scrub Support */}
                        <div className="position-relative w-100 mt-2" style={{ height: `${chartHeight + 35}px`, userSelect: 'none' }}>
                            {/* Y-Axis Grid Lines */}
                            <div className="position-absolute w-100 h-100 d-flex flex-column justify-content-between pointer-events-none" style={{ top: 0, left: 0 }}>
                                {[4, 3, 2, 1, 0].map((step, idx) => (
                                    <div key={idx} className="d-flex align-items-center w-100" style={{ height: '0px' }}>
                                        <span className="text-muted small pe-2" style={{ minWidth: '70px', fontSize: '10px', whiteSpace: 'nowrap', textAlign: 'right' }}>
                                            {formatRupiah((maxValue * step) / 4).replace(',00', '')}
                                        </span>
                                        <div className="flex-grow-1 border-bottom" style={{ borderColor: '#f1f5f9' }}></div>
                                    </div>
                                ))}
                            </div>

                            {/* Chart Canvas & SVG Container */}
                            <div 
                                className="position-relative" 
                                style={{ 
                                    marginLeft: '75px', 
                                    height: `${chartHeight}px`,
                                    touchAction: 'none',
                                    cursor: isDragging ? 'grabbing' : 'grab'
                                }}
                            >
                                <svg 
                                    ref={chartSvgRef}
                                    className="w-100 h-100" 
                                    viewBox={`0 0 ${chartWidth} ${chartHeight}`} 
                                    preserveAspectRatio="none"
                                    style={{ overflow: 'visible', display: 'block' }}
                                    onPointerDown={handlePointerDown}
                                    onPointerMove={handlePointerMove}
                                    onPointerUp={handlePointerUp}
                                    onPointerCancel={handlePointerUp}
                                >
                                    <defs>
                                        <linearGradient id="salesGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stopColor="#16a34a" stopOpacity="0.28" />
                                            <stop offset="100%" stopColor="#16a34a" stopOpacity="0.0" />
                                        </linearGradient>
                                        <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                                            <feGaussianBlur stdDeviation="3" result="blur" />
                                            <feComposite in="SourceGraphic" in2="blur" operator="over" />
                                        </filter>
                                    </defs>

                                    {/* Area Under Curve */}
                                    {splineArea && (
                                        <path d={splineArea} fill="url(#salesGrad)" />
                                    )}

                                    {/* Spline Line */}
                                    {splineLine && (
                                        <path 
                                            d={splineLine} 
                                            fill="none" 
                                            stroke="#15803d" 
                                            strokeWidth="2.8" 
                                            strokeLinecap="round" 
                                            strokeLinejoin="round" 
                                        />
                                    )}

                                    {/* Transparent Vertical Click / Hover Columns */}
                                    {svgPoints.map((pt, i) => {
                                        const colW = (chartWidth - paddingX * 2) / (svgPoints.length || 1);
                                        return (
                                            <rect
                                                key={`hitbox-${i}`}
                                                x={pt.x - colW / 2}
                                                y={0}
                                                width={colW}
                                                height={chartHeight}
                                                fill="transparent"
                                                style={{ cursor: isDragging ? 'grabbing' : 'pointer' }}
                                                onClick={() => handlePointClick(pt, i)}
                                                onMouseEnter={() => !isDragging && setHoveredPoint(i)}
                                            />
                                        );
                                    })}

                                    {/* Static Baseline Dots for all dates */}
                                    {svgPoints.map((pt, i) => (
                                        <circle
                                            key={`node-${i}`}
                                            cx={pt.x}
                                            cy={pt.y}
                                            r="4"
                                            fill="#ffffff"
                                            stroke="#15803d"
                                            strokeWidth="2"
                                            style={{ cursor: isDragging ? 'grabbing' : 'pointer' }}
                                            onClick={() => handlePointClick(pt, i)}
                                        />
                                    ))}

                                    {/* Silky-Smooth Gliding Active Indicator Assembly */}
                                    {activePoint && (
                                        <g style={{ pointerEvents: 'none' }}>
                                            {/* Vertical dashed guideline */}
                                            <line 
                                                x1={activePoint.x} 
                                                y1={activePoint.y} 
                                                x2={activePoint.x} 
                                                y2={chartHeight - paddingY} 
                                                stroke="#16a34a" 
                                                strokeWidth="1.8" 
                                                strokeDasharray="3 3" 
                                                style={{
                                                    transition: 'x1 0.2s cubic-bezier(0.22, 1, 0.36, 1), x2 0.2s cubic-bezier(0.22, 1, 0.36, 1), y1 0.2s cubic-bezier(0.22, 1, 0.36, 1)'
                                                }}
                                            />

                                            {/* Pulsing Glowing Halo */}
                                            <circle
                                                cx={activePoint.x}
                                                cy={activePoint.y}
                                                r="13"
                                                fill="#16a34a"
                                                fillOpacity="0.22"
                                                style={{
                                                    transition: 'cx 0.2s cubic-bezier(0.22, 1, 0.36, 1), cy 0.2s cubic-bezier(0.22, 1, 0.36, 1)'
                                                }}
                                            />

                                            {/* Outer Ring */}
                                            <circle
                                                cx={activePoint.x}
                                                cy={activePoint.y}
                                                r="7.5"
                                                fill="#16a34a"
                                                stroke="#ffffff"
                                                strokeWidth="2.5"
                                                style={{
                                                    filter: 'drop-shadow(0 2px 4px rgba(21, 128, 61, 0.4))',
                                                    transition: 'cx 0.2s cubic-bezier(0.22, 1, 0.36, 1), cy 0.2s cubic-bezier(0.22, 1, 0.36, 1)'
                                                }}
                                            />
                                        </g>
                                    )}
                                </svg>

                                {/* Floating Tooltip with smooth gliding transition and exact node tracking */}
                                {activePoint && (
                                    <div 
                                        className="position-absolute bg-dark text-white rounded-3 px-3 py-1.5 shadow-lg"
                                        style={{
                                            left: `${(activePoint.x / chartWidth) * 100}%`,
                                            top: `${(activePoint.y / chartHeight) * 100}%`,
                                            transform: 'translate(-50%, -115%)',
                                            zIndex: 20,
                                            fontSize: '11px',
                                            minWidth: '125px',
                                            textAlign: 'center',
                                            cursor: 'pointer',
                                            willChange: 'left, top',
                                            transition: 'left 0.2s cubic-bezier(0.22, 1, 0.36, 1), top 0.2s cubic-bezier(0.22, 1, 0.36, 1)',
                                            pointerEvents: isDragging ? 'none' : 'auto',
                                            boxShadow: '0 8px 20px -4px rgba(0, 0, 0, 0.45)'
                                        }}
                                        onClick={() => handleShowDetailModal(activePoint)}
                                        title="Klik untuk melihat rincian transaksi"
                                    >
                                        <div className="fw-semibold text-white-50" style={{ fontSize: '10px' }}>
                                            {activePoint.sales} pesanan (Klik Rincian)
                                        </div>
                                        <div className="fw-bold text-white fs-6" style={{ letterSpacing: '0.2px' }}>
                                            {formatRupiah(activePoint.value)}
                                        </div>
                                        {/* Little Arrow pointing directly down to circle node */}
                                        <div 
                                            className="position-absolute"
                                            style={{
                                                bottom: '-5px',
                                                left: '50%',
                                                transform: 'translateX(-50%)',
                                                width: 0,
                                                height: 0,
                                                borderLeft: '5px solid transparent',
                                                borderRight: '5px solid transparent',
                                                borderTop: '5px solid #212529'
                                            }}
                                        />
                                    </div>
                                )}
                            </div>

                            {/* X-Axis Labels (Clickable / Scrub Target) */}
                            <div className="d-flex justify-content-between pt-2 text-muted" style={{ marginLeft: '75px', fontSize: '10.5px' }}>
                                {chartPoints.map((p, idx) => {
                                    const isActive = activePoint && activePoint.originalIndex === idx;
                                    return (
                                        <span 
                                            key={idx} 
                                            onClick={() => handlePointClick(p, idx)}
                                            className={`cursor-pointer transition-all ${isActive ? 'fw-bold text-success scale-110' : 'hover-text-dark'}`}
                                            title={`Pilih ${p.label}`}
                                            style={{ cursor: 'pointer' }}
                                        >
                                            {p.label}
                                        </span>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Interactive Timeline Scrub Slider */}
                        <div className="mt-3 pt-2 border-top d-flex align-items-center gap-2" style={{ borderColor: '#f1f5f9' }}>
                            <div className="d-flex align-items-center gap-1.5 text-muted small" style={{ fontSize: '11px', whiteSpace: 'nowrap' }}>
                                <MoveHorizontal size={14} className="text-success" />
                                <span className="d-none d-sm-inline">Geser Timeline:</span>
                            </div>
                            <input 
                                type="range"
                                min="0"
                                max={Math.max(0, chartPoints.length - 1)}
                                value={activePoint?.originalIndex ?? (chartPoints.length - 1)}
                                onChange={(e) => {
                                    const idx = parseInt(e.target.value, 10);
                                    setSelectedPointIndex(idx);
                                    setHoveredPoint(idx);
                                }}
                                className="form-range flex-grow-1"
                                style={{ 
                                    accentColor: '#16a34a', 
                                    cursor: 'grab',
                                    height: '6px'
                                }}
                            />
                            <span className="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1 rounded-pill small" style={{ fontSize: '11px', whiteSpace: 'nowrap' }}>
                                📍 {activePoint?.label || '-'} : {formatRupiah(activePoint?.value || 0)}
                            </span>
                        </div>

                        {/* Interactive Selected Period Summary Bar */}
                        {activePoint && (
                            <div className="mt-3 p-2.5 bg-light rounded-3 border d-flex flex-wrap align-items-center justify-content-between gap-2 animate-fade-in" style={{ borderColor: '#e2e8f0' }}>
                                <div className="d-flex align-items-center gap-2">
                                    <div className="p-2 rounded-2 bg-success bg-opacity-10 text-success">
                                        <Calendar size={16} />
                                    </div>
                                    <div>
                                        <div className="d-flex align-items-center gap-1.5">
                                            <strong className="text-dark" style={{ fontSize: '12px' }}>{activePoint.label}</strong>
                                            <span className="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style={{ fontSize: '9.5px' }}>
                                                {viewMode === 'daily' ? 'Harian' : 'Bulanan'}
                                            </span>
                                        </div>
                                        <div className="text-muted" style={{ fontSize: '11px' }}>
                                            Omset: <strong className="text-success">{formatRupiah(activePoint.value)}</strong> • Transaksi: <strong className="text-dark">{activePoint.sales} Order</strong>
                                        </div>
                                    </div>
                                </div>

                                <div className="d-flex align-items-center gap-1.5">
                                    <button 
                                        onClick={() => handleShowDetailModal(activePoint)}
                                        className="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 d-flex align-items-center gap-1"
                                        style={{ fontSize: '11px' }}
                                    >
                                        <span>Rincian Modal</span>
                                        <ExternalLink size={12} />
                                    </button>
                                    <a 
                                        href="/admin/reports"
                                        className="btn btn-sm btn-agro rounded-pill px-2.5 py-1 d-flex align-items-center gap-1 shadow-sm"
                                        style={{ fontSize: '11px' }}
                                    >
                                        <span>Modul Laporan</span>
                                        <ArrowUpRight size={13} />
                                    </a>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* 2. Top Products Performance Activity Matrix / Heatmap */}
                <div className="col-lg-5">
                    <div className="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 className="fw-bold text-dark mb-0">Top Products</h6>
                                <small className="text-muted" style={{ fontSize: '11px' }}>Matriks intensitas penjualan 7 hari</small>
                            </div>
                            <span className="badge bg-light text-muted border rounded-pill py-1 px-2.5 small" style={{ fontSize: '11px' }}>
                                Daily Sales ▾
                            </span>
                        </div>

                        {/* Product Matrix Table */}
                        {productHeatmap && productHeatmap.length > 0 ? (
                            <div className="table-responsive">
                                <table className="table table-borderless align-middle mb-0" style={{ tableLayout: 'fixed' }}>
                                    <thead>
                                        <tr className="text-muted" style={{ fontSize: '10.5px' }}>
                                            <th style={{ width: '100px' }} className="fw-semibold ps-0">Produk</th>
                                            {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map((day, idx) => (
                                                <th key={idx} className="text-center fw-normal p-1">{day}</th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {productHeatmap.slice(0, 6).map((item, rIdx) => (
                                            <tr key={item.id || rIdx}>
                                                <td className="ps-0 py-1 text-truncate fw-semibold text-dark" style={{ fontSize: '11px' }} title={item.nama}>
                                                    {item.nama}
                                                </td>
                                                {item.days.map((d, cIdx) => {
                                                    let cellColor = '#f1f5f9';
                                                    if (d.level === 1) cellColor = '#86efac';
                                                    if (d.level === 2) cellColor = '#22c55e';
                                                    if (d.level === 3) cellColor = '#15803d';

                                                    return (
                                                        <td key={cIdx} className="text-center p-1">
                                                            <div 
                                                                className="rounded-2 d-inline-block"
                                                                style={{
                                                                    width: '18px',
                                                                    height: '18px',
                                                                    backgroundColor: cellColor,
                                                                    transition: 'transform 0.15s ease',
                                                                    cursor: 'pointer'
                                                                }}
                                                                title={`${item.nama} (${d.day})`}
                                                            />
                                                        </td>
                                                    );
                                                })}
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="text-center py-4 text-muted small">
                                Belum ada data transaksi produk pada periode ini.
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* BOTTOM ROW: BUDGET / TEFA USAGE, CUSTOMER REVIEW, LOW STOCK ALERT */}
            <div className="row g-3">
                {/* 1. TEFA Channel / Unit Omset Contribution */}
                <div className="col-lg-4 col-md-6">
                    <div className="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 className="fw-bold text-dark mb-0">Kontribusi Unit TEFA</h6>
                                <small className="text-muted" style={{ fontSize: '11px' }}>Realisasi Omset vs Target</small>
                            </div>
                            <span className="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 small" style={{ fontSize: '10.5px' }}>
                                4 Unit Usaha
                            </span>
                        </div>

                        {unitStats && unitStats.length > 0 ? (
                            <div className="d-flex flex-column gap-3">
                                {unitStats.map((u, i) => {
                                    const unitTarget = Number(u.target || 10000000);
                                    const unitOmset = Number(u.total_omset || u.orders_sum_total_akhir || 0);
                                    const pct = unitTarget > 0 ? Math.min(100, Math.round((unitOmset / unitTarget) * 100)) : 0;
                                    return (
                                        <div key={i} className="p-2 rounded-3 bg-light border" style={{ borderColor: 'var(--border-color)' }}>
                                            <div className="d-flex justify-content-between align-items-center mb-1.5">
                                                <span className="fw-semibold text-dark text-truncate" style={{ fontSize: '11.5px', maxWidth: '170px' }} title={u.nama_unit}>
                                                    {u.nama_unit}
                                                </span>
                                                <div className="text-end">
                                                    <span className="fw-bold text-success" style={{ fontSize: '11px' }}>{formatRupiah(unitOmset)}</span>
                                                    <span className="text-muted ms-1" style={{ fontSize: '10px' }}>({pct}%)</span>
                                                </div>
                                            </div>
                                            <div className="progress rounded-pill" style={{ height: '7px', backgroundColor: '#e2e8f0' }}>
                                                <div 
                                                    className="progress-bar rounded-pill bg-success" 
                                                    style={{ width: `${Math.max(2, pct)}%` }}
                                                    role="progressbar"
                                                    aria-valuenow={pct}
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                />
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="text-muted small py-3">Data kontribusi unit belum tersedia.</div>
                        )}

                        {/* Smart Tip Notice */}
                        <div className="mt-3 p-2.5 rounded-3 bg-light border d-flex align-items-start gap-2">
                            <Sparkles size={16} className="text-success mt-0.5 flex-shrink-0" />
                            <p className="text-muted mb-0" style={{ fontSize: '10.5px', lineHeight: '1.4' }}>
                                Data omset dan volume pesanan terakumulasi secara otomatis dari transaksi yang terverifikasi lunas.
                            </p>
                        </div>
                    </div>
                </div>

                {/* 2. Customer Reviews */}
                <div className="col-lg-4 col-md-6">
                    <div className="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <h6 className="fw-bold text-dark mb-0">Customer Review</h6>
                            <span className="badge bg-light text-muted border rounded-pill py-1 px-2 small" style={{ fontSize: '10px' }}>
                                Terbaru ▾
                            </span>
                        </div>

                        {recentReviews && recentReviews.length > 0 ? (
                            <div className="d-flex flex-column gap-3">
                                {recentReviews.slice(0, 2).map((rev, idx) => (
                                    <div key={rev.id || idx} className="border-bottom pb-2.5 last:border-0">
                                        <div className="d-flex align-items-center justify-content-between mb-1">
                                            <div className="d-flex align-items-center gap-2">
                                                <div 
                                                    className="rounded-circle bg-success bg-opacity-10 text-success fw-bold d-flex align-items-center justify-content-center"
                                                    style={{ width: '28px', height: '28px', fontSize: '11px' }}
                                                >
                                                    {rev.user_name?.charAt(0) || 'U'}
                                                </div>
                                                <div>
                                                    <span className="fw-bold text-dark d-block" style={{ fontSize: '11.5px' }}>{rev.user_name}</span>
                                                    <small className="text-muted" style={{ fontSize: '9.5px' }}>{rev.created_at}</small>
                                                </div>
                                            </div>
                                            <div className="d-flex text-warning">
                                                {[...Array(rev.rating || 5)].map((_, sIdx) => (
                                                    <Star key={sIdx} size={11} fill="#eab308" />
                                                ))}
                                            </div>
                                        </div>
                                        <p className="text-muted mb-0 fst-italic" style={{ fontSize: '11px', lineHeight: '1.35' }}>
                                            &ldquo;{rev.comment}&rdquo;
                                        </p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-4 text-muted small">
                                Belum ada ulasan produk dari pelanggan.
                            </div>
                        )}
                    </div>
                </div>

                {/* 3. Low Stock Alert & Restock Action */}
                <div className="col-lg-4 col-md-12">
                    <div className="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div className="d-flex justify-content-between align-items-center mb-2">
                                <h6 className="fw-bold text-danger mb-0 d-flex align-items-center gap-1.5">
                                    <AlertTriangle size={15} /> Low Stock Alert
                                </h6>
                                <span className="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5" style={{ fontSize: '10px' }}>
                                    Perlu Restok
                                </span>
                            </div>

                            {lowStockProducts && lowStockProducts.length > 0 ? (
                                <div>
                                    <div className="d-flex align-items-center gap-3 my-2 p-2 bg-light rounded-3 border">
                                        <div className="p-2 bg-white rounded-3 shadow-sm border text-success">
                                            <Package size={22} />
                                        </div>
                                        <div className="text-truncate flex-grow-1">
                                            <div className="fw-bold text-dark text-truncate" style={{ fontSize: '12px', maxWidth: '180px' }} title={lowStockProducts[0]?.nama_produk}>
                                                {lowStockProducts[0]?.nama_produk}
                                            </div>
                                            <small className="text-muted" style={{ fontSize: '10.5px' }}>
                                                Sisa Stok: <strong className="text-danger">{lowStockProducts[0]?.stok || 0} unit</strong> ({lowStockProducts[0]?.unit?.nama_unit || 'TEFA'})
                                            </small>
                                        </div>
                                    </div>
                                    <p className="text-muted mb-3" style={{ fontSize: '10.5px', lineHeight: '1.4' }}>
                                        Produk ini terjual cepat. Sesuaikan stok langsung dari sini atau buka katalog produk.
                                    </p>
                                </div>
                            ) : (
                                <div className="text-center py-3">
                                    <CheckCircle2 size={24} className="text-success mb-1" />
                                    <div className="text-muted small">Semua stok produk dalam kondisi aman (&gt; 10 unit).</div>
                                </div>
                            )}
                        </div>

                        {lowStockProducts && lowStockProducts.length > 0 && onQuickStock ? (
                            <div className="d-flex gap-2 mt-2">
                                <button 
                                    onClick={() => onQuickStock(lowStockProducts[0])}
                                    className="btn btn-agro flex-grow-1 rounded-pill py-1.5 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-1.5"
                                    style={{ fontSize: '12px' }}
                                >
                                    <span>⚡ Restok Cepat</span>
                                </button>
                                <a 
                                    href={`/admin/products?search=${encodeURIComponent(lowStockProducts[0]?.nama_produk || '')}`} 
                                    className="btn btn-outline-secondary rounded-pill py-1.5 px-3 d-flex align-items-center justify-content-center"
                                    title="Buka di Katalog"
                                    style={{ fontSize: '12px' }}
                                >
                                    <ArrowUpRight size={14} />
                                </a>
                            </div>
                        ) : (
                            <a 
                                href="/admin/products" 
                                className="btn btn-outline-secondary w-100 rounded-pill py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-2 mt-2"
                                style={{ fontSize: '12px' }}
                            >
                                <span>Buka Katalog Produk</span>
                                <ArrowUpRight size={14} />
                            </a>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SalesAnalyticsOverview;
