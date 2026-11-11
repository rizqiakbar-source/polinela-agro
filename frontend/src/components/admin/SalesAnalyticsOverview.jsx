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
    ExternalLink,
    MoveHorizontal,
    MapPin,
    Zap
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

    // Calculate dynamic nice maximum for Revenue (Left Y-Axis)
    const dataMaxRevenue = Math.max(...chartPoints.map(p => p.value), 0);
    const calculateNiceMax = (val) => {
        if (val <= 0) return 100000;
        const target = val * 1.2;
        const magnitude = Math.pow(10, Math.floor(Math.log10(target)));
        const ratio = target / magnitude;
        let factor = 10;
        if (ratio <= 1.25) factor = 1.25;
        else if (ratio <= 1.5) factor = 1.5;
        else if (ratio <= 2) factor = 2;
        else if (ratio <= 2.5) factor = 2.5;
        else if (ratio <= 5) factor = 5;
        else if (ratio <= 7.5) factor = 7.5;
        return Math.ceil(factor * magnitude);
    };
    const maxRevenue = calculateNiceMax(dataMaxRevenue);

    // Calculate dynamic nice maximum for Orders (Right Y-Axis)
    const dataMaxOrders = Math.max(...chartPoints.map(p => p.sales), 0);
    const maxOrders = dataMaxOrders > 0 ? Math.max(Math.ceil(dataMaxOrders * 1.25), 4) : 4;

    // Mini KPI summary metrics for the period
    const periodRevenue = chartPoints.reduce((acc, p) => acc + p.value, 0);
    const periodOrders = chartPoints.reduce((acc, p) => acc + p.sales, 0);
    const aov = periodOrders > 0 ? Math.round(periodRevenue / periodOrders) : 0;

    // Exact SVG Dimensions & Margins
    const chartWidth = 640;
    const chartHeight = 220;
    const margin = { top: 25, right: 35, bottom: 35, left: 75 };
    const plotWidth = chartWidth - margin.left - margin.right;
    const plotHeight = chartHeight - margin.top - margin.bottom;
    const baselineY = margin.top + plotHeight; // Solid baseline (Rp 0 & 0 Order)

    // Generate accurate data points mapped to SVG plot
    const getSvgPoints = () => {
        const n = chartPoints.length;
        if (n === 0) return [];
        const stepX = plotWidth / (n - 1 || 1);
        const colW = n > 1 ? stepX : plotWidth;
        const barWidth = Math.min(46, Math.max(36, colW * 0.52));

        return chartPoints.map((p, i) => {
            const x = margin.left + i * stepX;
            // Revenue mapping
            const ratioRev = maxRevenue > 0 ? (p.value / maxRevenue) : 0;
            const y = baselineY - (ratioRev * plotHeight);

            // Order bar mapping (soft pastel mint rounded pill bar)
            const ratioSales = maxOrders > 0 ? (p.sales / maxOrders) : 0;
            const barH = ratioSales * (plotHeight * 0.85);
            const barX = x - barWidth / 2;
            const barY = baselineY - barH;

            return { 
                ...p, 
                x, 
                y, 
                barX, 
                barY, 
                barH, 
                barWidth, 
                colW, 
                originalIndex: i 
            };
        });
    };

    const svgPoints = getSvgPoints();

    // Generate Monotone Cubic Spline to avoid artificial overshooting
    const generateSplinePath = (pts) => {
        if (!pts || pts.length === 0) return '';
        if (pts.length === 1) return `M ${pts[0].x.toFixed(2)} ${pts[0].y.toFixed(2)}`;
        if (pts.length === 2) return `M ${pts[0].x.toFixed(2)},${pts[0].y.toFixed(2)} L ${pts[1].x.toFixed(2)},${pts[1].y.toFixed(2)}`;

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
        ? `${splineLine} L ${svgPoints[svgPoints.length - 1].x.toFixed(2)},${baselineY} L ${svgPoints[0].x.toFixed(2)},${baselineY} Z`
        : '';

    // Active tooltip point default to hovered, selected, or last item
    const activePointIndex = hoveredPoint !== null 
        ? hoveredPoint 
        : (selectedPointIndex !== null ? selectedPointIndex : (svgPoints.length > 0 ? svgPoints.length - 1 : null));

    const activePoint = activePointIndex !== null && svgPoints[activePointIndex] ? svgPoints[activePointIndex] : null;

    // Timeline Slider track fill percentage matching mockup
    const sliderPercent = chartPoints.length > 1 
        ? ((activePoint?.originalIndex ?? (chartPoints.length - 1)) / (chartPoints.length - 1)) * 100 
        : 100;

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
            title: `Rincian Penjualan`,
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
                const targetUrl = pt.date ? `/admin/reports?start_date=${pt.date}&end_date=${pt.date}` : '/admin/reports';
                window.location.href = targetUrl;
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
                    <div className="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative">
                        <style>{`
                            .custom-mock-slider {
                                -webkit-appearance: none;
                                appearance: none;
                                width: 100%;
                                height: 6px;
                                border-radius: 9999px;
                                outline: none;
                                cursor: pointer;
                                transition: background 0.15s ease;
                            }
                            .custom-mock-slider::-webkit-slider-thumb {
                                -webkit-appearance: none;
                                appearance: none;
                                width: 18px;
                                height: 18px;
                                border-radius: 50%;
                                background: radial-gradient(circle, #0284c7 40%, #0f766e 100%);
                                cursor: pointer;
                                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
                                border: 2.5px solid #ffffff;
                                transition: transform 0.15s ease, box-shadow 0.15s ease;
                            }
                            .custom-mock-slider::-webkit-slider-thumb:hover {
                                transform: scale(1.18);
                                box-shadow: 0 0 0 5px rgba(2, 132, 199, 0.2);
                            }
                            .custom-mock-slider::-moz-range-thumb {
                                width: 18px;
                                height: 18px;
                                border-radius: 50%;
                                background: radial-gradient(circle, #0284c7 40%, #0f766e 100%);
                                cursor: pointer;
                                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
                                border: 2.5px solid #ffffff;
                                transition: transform 0.15s ease, box-shadow 0.15s ease;
                            }
                            .custom-mock-slider::-moz-range-thumb:hover {
                                transform: scale(1.18);
                                box-shadow: 0 0 0 5px rgba(2, 132, 199, 0.2);
                            }
                        `}</style>

                        {/* Card Header matching Mockup */}
                        <div className="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <div>
                                <h5 className="fw-bold text-dark mb-1" style={{ letterSpacing: '-0.2px' }}>
                                    Sales Analytics
                                </h5>
                                <small className="text-muted" style={{ fontSize: '12px' }}>
                                    Grafik dinamika omset dan kuantitas transaksi
                                </small>
                            </div>
                            <div className="d-flex align-items-center gap-2">
                                <select 
                                    className="form-select form-select-sm border rounded-pill small py-1.5 px-3 bg-white"
                                    value={viewMode}
                                    onChange={(e) => {
                                        setViewMode(e.target.value);
                                        setSelectedPointIndex(null);
                                        setHoveredPoint(null);
                                    }}
                                    style={{ fontSize: '12px', width: 'auto', fontWeight: '500', borderColor: '#e2e8f0' }}
                                >
                                    <option value="daily">Daily Sales</option>
                                    <option value="monthly">Monthly Sales</option>
                                </select>
                                <span className="badge bg-white text-muted border rounded-pill py-1.5 px-3 small d-inline-flex align-items-center gap-1.5" style={{ fontSize: '12px', borderColor: '#e2e8f0' }}>
                                    <Calendar size={13} className="text-muted" /> {chartPoints[0]?.label || '-'} – {chartPoints[chartPoints.length - 1]?.label || '-'} 2026
                                </span>
                            </div>
                        </div>

                        {/* MINI KPI BAR (Summary of Current Period: 3 Centered Columns matching Mockup) */}
                        <div className="card border rounded-4 p-2.5 mb-4 bg-white shadow-xs" style={{ borderColor: '#e2e8f0' }}>
                            <div className="row text-center align-items-center g-0">
                                {/* 1. Total Omset */}
                                <div className="col-4 border-end px-1">
                                    <div className="text-muted mb-0.5" style={{ fontSize: '11px' }}>Total Omset</div>
                                    <div className="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                        <span className="fw-bold text-dark" style={{ fontSize: '15px' }}>{formatRupiah(periodRevenue)}</span>
                                        <span className="badge rounded-pill bg-success-subtle text-success fw-semibold px-1.5 py-0.5" style={{ fontSize: '9.5px' }}>
                                            ▲ {stats?.growth_revenue || '+5%'}
                                        </span>
                                    </div>
                                </div>

                                {/* 2. Total Pesanan */}
                                <div className="col-4 border-end px-1">
                                    <div className="text-muted mb-0.5" style={{ fontSize: '11px' }}>Total Pesanan</div>
                                    <div className="fw-bold text-dark" style={{ fontSize: '15px' }}>{periodOrders} Pesanan</div>
                                </div>

                                {/* 3. Rata-rata Transaksi */}
                                <div className="col-4 px-1">
                                    <div className="text-muted mb-0.5" style={{ fontSize: '11px' }}>Rata-rata Transaksi</div>
                                    <div className="fw-bold text-dark" style={{ fontSize: '15px' }}>{formatRupiah(aov)}</div>
                                </div>
                            </div>
                        </div>

                        {/* Interactive Combo Chart (Smooth Gradient Area + Rounded Bar Chart) */}
                        <div className="position-relative w-100 overflow-hidden" style={{ height: `${chartHeight + 10}px`, userSelect: 'none' }}>
                            {/* SVG Plot Area */}
                            <svg 
                                ref={chartSvgRef}
                                className="w-100 h-100" 
                                viewBox={`0 0 ${chartWidth} ${chartHeight}`} 
                                preserveAspectRatio="none"
                                style={{ overflow: 'hidden', display: 'block', touchAction: 'none', cursor: isDragging ? 'grabbing' : 'default' }}
                                onPointerDown={handlePointerDown}
                                onPointerMove={handlePointerMove}
                                onPointerUp={handlePointerUp}
                                onPointerCancel={handlePointerUp}
                            >
                                <defs>
                                    {/* Soft Mint Pastel Bar Gradient */}
                                    <linearGradient id="mintBarGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stopColor="#a7f3d0" stopOpacity="0.80" />
                                        <stop offset="100%" stopColor="#d1fae5" stopOpacity="0.45" />
                                    </linearGradient>
                                    <linearGradient id="mintBarGradActive" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stopColor="#86efac" stopOpacity="0.95" />
                                        <stop offset="100%" stopColor="#bbf7d0" stopOpacity="0.65" />
                                    </linearGradient>

                                    {/* Smooth Emerald Area Gradient matching Mockup */}
                                    <linearGradient id="mockAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stopColor="#15803d" stopOpacity="0.15" />
                                        <stop offset="100%" stopColor="#15803d" stopOpacity="0.01" />
                                    </linearGradient>
                                </defs>

                                {/* 1. Gridlines & Sumbu Y Labels */}
                                {[4, 3, 2, 1, 0].map((step) => {
                                    const yPos = margin.top + ((4 - step) / 4) * plotHeight;
                                    const revVal = (maxRevenue * step) / 4;
                                    const isBaseline = step === 0;

                                    return (
                                        <g key={`gridline-${step}`}>
                                            {/* Horizontal Dashed Grid Line */}
                                            <line 
                                                x1={margin.left}
                                                y1={yPos}
                                                x2={chartWidth - margin.right}
                                                y2={yPos}
                                                stroke="#e2e8f0"
                                                strokeWidth="1"
                                                strokeDasharray="4 4"
                                            />
                                            {/* Sumbu Y Kiri (Omset Rp) */}
                                            <text
                                                x={margin.left - 10}
                                                y={yPos + 3.5}
                                                textAnchor="end"
                                                fontSize="10.5"
                                                fill="#64748b"
                                                fontWeight={isBaseline ? "600" : "400"}
                                                fontFamily="system-ui, -apple-system, sans-serif"
                                            >
                                                {formatRupiah(revVal).replace(',00', '')}
                                            </text>
                                        </g>
                                    );
                                })}

                                {/* 2. Active Point Vertical Dashed Guideline */}
                                {activePoint && (
                                    <line 
                                        x1={activePoint.x} 
                                        y1={margin.top} 
                                        x2={activePoint.x} 
                                        y2={baselineY} 
                                        stroke="#cbd5e1" 
                                        strokeWidth="1.2" 
                                        strokeDasharray="3 3" 
                                        style={{
                                            transition: 'x1 0.18s cubic-bezier(0.22, 1, 0.36, 1), x2 0.18s cubic-bezier(0.22, 1, 0.36, 1)'
                                        }}
                                    />
                                )}

                                {/* 3. Rounded Bar Chart (Soft Mint Green Pill Bars matching Mockup) */}
                                {svgPoints.map((pt, i) => {
                                    const isActive = activePoint && activePoint.originalIndex === i;
                                    if (pt.sales <= 0) return null;
                                    return (
                                        <rect
                                            key={`bar-${i}`}
                                            x={pt.barX}
                                            y={pt.barY}
                                            width={pt.barWidth}
                                            height={Math.max(6, pt.barH)}
                                            rx="8"
                                            ry="8"
                                            fill={isActive ? "url(#mintBarGradActive)" : "url(#mintBarGrad)"}
                                            style={{ transition: 'all 0.2s ease', cursor: 'pointer' }}
                                            onClick={() => handlePointClick(pt, i)}
                                        />
                                    );
                                })}

                                {/* 4. Smooth Area Under Curve (Omset Rp) */}
                                {splineArea && (
                                    <path 
                                        d={splineArea} 
                                        fill="url(#mockAreaGrad)" 
                                        style={{ transition: 'd 0.3s ease' }}
                                    />
                                )}

                                {/* 5. Smooth Spline Curve Line (Omset Rp) */}
                                {splineLine && (
                                    <path 
                                        d={splineLine} 
                                        fill="none" 
                                        stroke="#15803d" 
                                        strokeWidth="2.8" 
                                        strokeLinecap="round" 
                                        strokeLinejoin="round" 
                                        style={{ transition: 'd 0.3s ease' }}
                                    />
                                )}

                                {/* 6. Transparent Hitbox Columns for Smooth Hover & Scrub */}
                                {svgPoints.map((pt, i) => (
                                    <rect
                                        key={`hitbox-${i}`}
                                        x={pt.x - pt.colW / 2}
                                        y={margin.top}
                                        width={pt.colW}
                                        height={plotHeight}
                                        fill="transparent"
                                        style={{ cursor: isDragging ? 'grabbing' : 'pointer' }}
                                        onClick={() => handlePointClick(pt, i)}
                                        onMouseEnter={() => !isDragging && setHoveredPoint(i)}
                                    />
                                ))}

                                {/* 7. Active Point Node (Only on the active point matching Mockup) */}
                                {activePoint && (
                                    <g style={{ pointerEvents: 'none' }}>
                                        {/* Soft Halo */}
                                        <circle
                                            cx={activePoint.x}
                                            cy={activePoint.y}
                                            r="8"
                                            fill="#15803d"
                                            fillOpacity="0.18"
                                            style={{
                                                transition: 'cx 0.18s cubic-bezier(0.22, 1, 0.36, 1), cy 0.18s cubic-bezier(0.22, 1, 0.36, 1)'
                                            }}
                                        />

                                        {/* Outer Ring & Center Node */}
                                        <circle
                                            cx={activePoint.x}
                                            cy={activePoint.y}
                                            r="4.5"
                                            fill="#15803d"
                                            stroke="#ffffff"
                                            strokeWidth="2.5"
                                            style={{
                                                filter: 'drop-shadow(0 1px 3px rgba(0, 0, 0, 0.25))',
                                                transition: 'cx 0.18s cubic-bezier(0.22, 1, 0.36, 1), cy 0.18s cubic-bezier(0.22, 1, 0.36, 1)'
                                            }}
                                        />
                                    </g>
                                )}

                                {/* 8. Sumbu X Date Labels (Positioned neatly BELOW the Rp 0 baseline) */}
                                {svgPoints.map((pt, idx) => {
                                    const isActive = activePoint && activePoint.originalIndex === idx;
                                    return (
                                        <text
                                            key={`label-${idx}`}
                                            x={pt.x}
                                            y={baselineY + 22}
                                            textAnchor="middle"
                                            fontSize="11"
                                            fill={isActive ? "#0f172a" : "#64748b"}
                                            fontWeight={isActive ? "700" : "400"}
                                            fontFamily="system-ui, -apple-system, sans-serif"
                                            style={{ cursor: 'pointer', transition: 'all 0.15s ease' }}
                                            onClick={() => handlePointClick(pt, idx)}
                                        >
                                            {pt.label}
                                        </text>
                                    );
                                })}
                            </svg>

                            {/* Floating Tooltip matching Mockup */}
                            {activePoint && (
                                <div 
                                    className="position-absolute text-white rounded-3 px-3 py-1.5 shadow-lg"
                                    style={{
                                        left: `${(activePoint.x / chartWidth) * 100}%`,
                                        top: `${(Math.max(10, activePoint.y) / chartHeight) * 100}%`,
                                        transform: 'translate(-50%, -125%)',
                                        zIndex: 20,
                                        minWidth: '130px',
                                        textAlign: 'center',
                                        cursor: 'pointer',
                                        willChange: 'left, top',
                                        transition: 'left 0.18s cubic-bezier(0.22, 1, 0.36, 1), top 0.18s cubic-bezier(0.22, 1, 0.36, 1)',
                                        pointerEvents: isDragging ? 'none' : 'auto',
                                        backgroundColor: '#1e293b',
                                        boxShadow: '0 8px 20px rgba(0, 0, 0, 0.35)'
                                    }}
                                    onClick={() => handleShowDetailModal(activePoint)}
                                    title="Klik untuk melihat rincian transaksi"
                                >
                                    <div className="text-white-50" style={{ fontSize: '10.5px', fontWeight: '500' }}>
                                        {activePoint.label}
                                    </div>
                                    <div className="fw-bold text-white" style={{ fontSize: '12px', marginTop: '1px', letterSpacing: '0.2px' }}>
                                        {formatRupiah(activePoint.value)} ({activePoint.sales} Pesanan)
                                    </div>

                                    {/* Downward triangle arrow */}
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
                                            borderTop: '5px solid #1e293b'
                                        }}
                                    />
                                </div>
                            )}
                        </div>

                        {/* Custom Modern Timeline Scrub Slider matching Mockup */}
                        <div className="mt-3 pt-3 border-top d-flex align-items-center gap-2" style={{ borderColor: '#f1f5f9' }}>
                            <div className="d-flex align-items-center gap-1.5 text-muted small" style={{ fontSize: '12px', whiteSpace: 'nowrap' }}>
                                <MoveHorizontal size={14} className="text-secondary" />
                                <span className="fw-medium text-dark">Geser Timeline:</span>
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
                                className="custom-mock-slider flex-grow-1"
                                style={{
                                    background: `linear-gradient(to right, #0f766e 0%, #0f766e ${sliderPercent}%, #e2e8f0 ${sliderPercent}%, #e2e8f0 100%)`
                                }}
                                title="Geser untuk memilih tanggal"
                            />
                            <span className="badge rounded-pill fw-bold px-3 py-1.5 small d-inline-flex align-items-center gap-1" style={{ fontSize: '11px', whiteSpace: 'nowrap', backgroundColor: '#ecfdf5', color: '#059669' }}>
                                <MapPin size={11} /> {activePoint?.label || '-'} : {formatRupiah(activePoint?.value || 0)}
                            </span>
                        </div>

                        {/* Interactive Selected Period Summary Bar matching Mockup */}
                        {activePoint && (
                            <div className="mt-3 p-3 bg-light rounded-4 border d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-xs" style={{ borderColor: '#e2e8f0', transition: 'all 0.2s ease' }}>
                                <div className="d-flex align-items-center gap-2.5">
                                    <div className="p-2.5 rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center">
                                        <Calendar size={18} />
                                    </div>
                                    <div>
                                        <div className="d-flex align-items-center gap-1.5">
                                            <strong className="text-dark" style={{ fontSize: '13px' }}>{activePoint.label}</strong>
                                            <span className="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 fw-semibold" style={{ fontSize: '9.5px' }}>
                                                {viewMode === 'daily' ? 'Harian' : 'Bulanan'}
                                            </span>
                                        </div>
                                        <div className="text-muted" style={{ fontSize: '11.5px' }}>
                                            Omset: <strong className="text-dark">{formatRupiah(activePoint.value)}</strong> • Transaksi: <strong className="text-dark">{activePoint.sales} Order</strong>
                                        </div>
                                    </div>
                                </div>

                                <div className="d-flex align-items-center gap-2">
                                    <button 
                                        onClick={() => handleShowDetailModal(activePoint)}
                                        className="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-1.5 fw-medium text-secondary bg-white"
                                        style={{ fontSize: '12px', borderColor: '#cbd5e1' }}
                                    >
                                        <span>Rincian Modal</span>
                                        <ExternalLink size={13} />
                                    </button>
                                    <a 
                                        href="/admin/reports"
                                        className="btn btn-sm rounded-pill px-3.5 py-1.5 d-flex align-items-center gap-1.5 text-white fw-medium shadow-xs"
                                        style={{ fontSize: '12px', backgroundColor: '#0f766e' }}
                                    >
                                        <span>Modul Laporan</span>
                                        <ArrowUpRight size={14} />
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
                                            <th style={{ width: '105px' }} className="fw-semibold ps-0 text-truncate">Produk</th>
                                            {[
                                                { label: 'S', full: 'Senin' },
                                                { label: 'S', full: 'Selasa' },
                                                { label: 'R', full: 'Rabu' },
                                                { label: 'K', full: 'Kamis' },
                                                { label: 'J', full: 'Jumat' },
                                                { label: 'S', full: 'Sabtu' },
                                                { label: 'M', full: 'Minggu' }
                                            ].map((day, idx) => (
                                                <th key={idx} className="text-center fw-semibold p-1" style={{ fontSize: '10.5px', color: '#64748b' }} title={day.full}>
                                                    {day.label}
                                                </th>
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
                                    <Zap size={13} />
                                    <span>Restok Cepat</span>
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
