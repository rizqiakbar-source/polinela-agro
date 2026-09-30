import React, { useState, useRef, useEffect, useCallback } from 'react';
import { 
    ZoomIn, ZoomOut, RotateCw, RotateCcw, Crop, Check, X, 
    Move, RefreshCw, Circle, Square, Sparkles, Image as ImageIcon 
} from 'lucide-react';

const ImageCropperModal = ({
    isOpen,
    imageSrc,
    onClose,
    onCropComplete,
    cropShapeDefault = 'circle',
}) => {
    const canvasRef = useRef(null);
    const previewCircleRef = useRef(null);
    const previewSquareRef = useRef(null);
    const imageObjRef = useRef(null);

    const [zoom, setZoom] = useState(1);
    const [rotation, setRotation] = useState(0); // degrees: 0, 90, 180, 270
    const [pan, setPan] = useState({ x: 0, y: 0 });
    const [isDragging, setIsDragging] = useState(false);
    const [dragStart, setDragStart] = useState({ x: 0, y: 0 });
    const [cropShape, setCropShape] = useState(cropShapeDefault); // 'circle' | 'square'
    const [imageLoaded, setImageLoaded] = useState(false);
    const [imageDimensions, setImageDimensions] = useState({ width: 0, height: 0 });

    const CANVAS_SIZE = 340; // Virtual interactive box size
    const CROP_BOX_SIZE = 260; // Inner crop region

    // Load image when imageSrc changes
    useEffect(() => {
        if (!isOpen || !imageSrc) return;

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            imageObjRef.current = img;
            setImageDimensions({ width: img.naturalWidth, height: img.naturalHeight });
            setImageLoaded(true);
            setZoom(1);
            setRotation(0);
            setPan({ x: 0, y: 0 });
        };
        img.src = imageSrc;

        return () => {
            imageObjRef.current = null;
            setImageLoaded(false);
        };
    }, [isOpen, imageSrc]);

    // Draw the main interactive canvas
    const drawMainCanvas = useCallback(() => {
        const canvas = canvasRef.current;
        const img = imageObjRef.current;
        if (!canvas || !img || !imageLoaded) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        ctx.clearRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

        // Calculate initial scale to fit crop box
        const scaleToFit = Math.max(CROP_BOX_SIZE / img.naturalWidth, CROP_BOX_SIZE / img.naturalHeight);
        const currentScale = scaleToFit * zoom;

        // Draw transformed image
        ctx.save();
        ctx.translate(CANVAS_SIZE / 2 + pan.x, CANVAS_SIZE / 2 + pan.y);
        ctx.rotate((rotation * Math.PI) / 180);
        ctx.scale(currentScale, currentScale);
        ctx.drawImage(
            img,
            -img.naturalWidth / 2,
            -img.naturalHeight / 2,
            img.naturalWidth,
            img.naturalHeight
        );
        ctx.restore();

        // Draw Dark Mask Overlay around crop region
        ctx.save();
        ctx.fillStyle = 'rgba(15, 23, 42, 0.72)';
        ctx.beginPath();
        ctx.rect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

        const cropX = (CANVAS_SIZE - CROP_BOX_SIZE) / 2;
        const cropY = (CANVAS_SIZE - CROP_BOX_SIZE) / 2;

        if (cropShape === 'circle') {
            const centerX = CANVAS_SIZE / 2;
            const centerY = CANVAS_SIZE / 2;
            const radius = CROP_BOX_SIZE / 2;
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2, true);
        } else {
            ctx.rect(cropX + CROP_BOX_SIZE, cropY, -CROP_BOX_SIZE, CROP_BOX_SIZE);
        }
        ctx.fill();
        ctx.restore();

        // Draw Crop Border & Grid
        ctx.save();
        ctx.strokeStyle = '#22c55e';
        ctx.lineWidth = 2;
        ctx.setLineDash([]);

        if (cropShape === 'circle') {
            ctx.beginPath();
            ctx.arc(CANVAS_SIZE / 2, CANVAS_SIZE / 2, CROP_BOX_SIZE / 2, 0, Math.PI * 2);
            ctx.stroke();
        } else {
            ctx.strokeRect(cropX, cropY, CROP_BOX_SIZE, CROP_BOX_SIZE);
        }

        // Rule of thirds grid lines
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
        ctx.lineWidth = 1;
        ctx.setLineDash([4, 4]);

        const third = CROP_BOX_SIZE / 3;
        // Horizontal lines
        ctx.beginPath();
        ctx.moveTo(cropX, cropY + third);
        ctx.lineTo(cropX + CROP_BOX_SIZE, cropY + third);
        ctx.moveTo(cropX, cropY + third * 2);
        ctx.lineTo(cropX + CROP_BOX_SIZE, cropY + third * 2);
        // Vertical lines
        ctx.moveTo(cropX + third, cropY);
        ctx.lineTo(cropX + third, cropY + CROP_BOX_SIZE);
        ctx.moveTo(cropX + third * 2, cropY);
        ctx.lineTo(cropX + third * 2, cropY + CROP_BOX_SIZE);
        ctx.stroke();

        ctx.restore();

        // Update previews
        drawPreviews(currentScale);
    }, [imageLoaded, zoom, rotation, pan, cropShape]);

    // Draw live previews
    const drawPreviews = (currentScale) => {
        const img = imageObjRef.current;
        if (!img) return;

        const updatePreview = (previewCanvas, isCircle) => {
            if (!previewCanvas) return;
            const ctx = previewCanvas.getContext('2d');
            if (!ctx) return;

            const size = previewCanvas.width;
            ctx.clearRect(0, 0, size, size);

            ctx.save();
            if (isCircle) {
                ctx.beginPath();
                ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
                ctx.clip();
            }

            const previewScaleRatio = size / CROP_BOX_SIZE;
            ctx.translate(size / 2 + pan.x * previewScaleRatio, size / 2 + pan.y * previewScaleRatio);
            ctx.rotate((rotation * Math.PI) / 180);
            ctx.scale(currentScale * previewScaleRatio, currentScale * previewScaleRatio);
            ctx.drawImage(
                img,
                -img.naturalWidth / 2,
                -img.naturalHeight / 2,
                img.naturalWidth,
                img.naturalHeight
            );
            ctx.restore();
        };

        updatePreview(previewCircleRef.current, true);
        updatePreview(previewSquareRef.current, false);
    };

    useEffect(() => {
        drawMainCanvas();
    }, [drawMainCanvas]);

    // Mouse & Touch Drag Handlers
    const handleMouseDown = (e) => {
        setIsDragging(true);
        setDragStart({ x: e.clientX - pan.x, y: e.clientY - pan.y });
    };

    const handleMouseMove = (e) => {
        if (!isDragging) return;
        setPan({
            x: e.clientX - dragStart.x,
            y: e.clientY - dragStart.y,
        });
    };

    const handleMouseUp = () => {
        setIsDragging(false);
    };

    const handleWheel = (e) => {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        setZoom((prev) => Math.min(Math.max(1, +(prev + delta).toFixed(2)), 4));
    };

    // Touch events for mobile
    const handleTouchStart = (e) => {
        if (e.touches.length === 1) {
            setIsDragging(true);
            setDragStart({
                x: e.touches[0].clientX - pan.x,
                y: e.touches[0].clientY - pan.y,
            });
        }
    };

    const handleTouchMove = (e) => {
        if (!isDragging || e.touches.length !== 1) return;
        setPan({
            x: e.touches[0].clientX - dragStart.x,
            y: e.touches[0].clientY - dragStart.y,
        });
    };

    const handleTouchEnd = () => {
        setIsDragging(false);
    };

    // Reset crop state
    const handleReset = () => {
        setZoom(1);
        setRotation(0);
        setPan({ x: 0, y: 0 });
    };

    // Rotate Clockwise
    const handleRotateCw = () => {
        setRotation((prev) => (prev + 90) % 360);
    };

    // Rotate Counter-Clockwise
    const handleRotateCcw = () => {
        setRotation((prev) => (prev - 90 + 360) % 360);
    };

    // Final Crop Export
    const handleApplyCrop = () => {
        const img = imageObjRef.current;
        if (!img) return;

        const OUTPUT_SIZE = 600; // High resolution square export
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = OUTPUT_SIZE;
        exportCanvas.height = OUTPUT_SIZE;
        const ctx = exportCanvas.getContext('2d');
        if (!ctx) return;

        const scaleToFit = Math.max(CROP_BOX_SIZE / img.naturalWidth, CROP_BOX_SIZE / img.naturalHeight);
        const currentScale = scaleToFit * zoom;
        const exportScaleRatio = OUTPUT_SIZE / CROP_BOX_SIZE;

        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        // Transform into export canvas
        ctx.save();
        ctx.translate(
            OUTPUT_SIZE / 2 + pan.x * exportScaleRatio,
            OUTPUT_SIZE / 2 + pan.y * exportScaleRatio
        );
        ctx.rotate((rotation * Math.PI) / 180);
        ctx.scale(currentScale * exportScaleRatio, currentScale * exportScaleRatio);
        ctx.drawImage(
            img,
            -img.naturalWidth / 2,
            -img.naturalHeight / 2,
            img.naturalWidth,
            img.naturalHeight
        );
        ctx.restore();

        exportCanvas.toBlob(
            (blob) => {
                if (!blob) return;
                const dataUrl = exportCanvas.toDataURL('image/jpeg', 0.92);
                const file = new File([blob], `avatar_cropped_${Date.now()}.jpg`, {
                    type: 'image/jpeg',
                });
                onCropComplete(blob, dataUrl, file);
                onClose();
            },
            'image/jpeg',
            0.92
        );
    };

    if (!isOpen) return null;

    return (
        <div 
            className="modal show d-block animate-fade-in" 
            style={{ backgroundColor: 'rgba(15, 23, 42, 0.75)', zIndex: 1060, backdropFilter: 'blur(6px)' }}
            tabIndex="-1"
        >
            <div className="modal-dialog modal-dialog-centered modal-lg">
                <div className="modal-content rounded-4 border-0 shadow-2xl overflow-hidden" style={{ background: 'var(--bg-card)', color: 'var(--text-body)' }}>
                    
                    {/* Modal Header */}
                    <div className="modal-header border-bottom px-4 py-3" style={{ borderColor: 'var(--border-color)' }}>
                        <div className="d-flex align-items-center gap-2">
                            <div className="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                                <Crop size={20} />
                            </div>
                            <div>
                                <h5 className="modal-title fw-bold mb-0" style={{ color: 'var(--text-heading)' }}>
                                    Editor & Crop Foto Profil
                                </h5>
                                <small className="text-muted">Sesuaikan posisi, zoom, dan sudut foto agar rapi & simetris</small>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            className="btn-close" 
                            onClick={onClose}
                            style={{ filter: 'var(--bs-btn-close-filter, none)' }}
                        ></button>
                    </div>

                    {/* Modal Body */}
                    <div className="modal-body p-4">
                        <div className="row g-4 align-items-center">
                            
                            {/* Interactive Canvas Viewport */}
                            <div className="col-lg-7 d-flex flex-column align-items-center justify-content-center">
                                <div 
                                    className="position-relative rounded-4 overflow-hidden shadow-sm"
                                    style={{ 
                                        width: `${CANVAS_SIZE}px`, 
                                        height: `${CANVAS_SIZE}px`,
                                        background: '#090d16',
                                        cursor: isDragging ? 'grabbing' : 'grab',
                                        touchAction: 'none'
                                    }}
                                    onMouseDown={handleMouseDown}
                                    onMouseMove={handleMouseMove}
                                    onMouseUp={handleMouseUp}
                                    onMouseLeave={handleMouseUp}
                                    onWheel={handleWheel}
                                    onTouchStart={handleTouchStart}
                                    onTouchMove={handleTouchMove}
                                    onTouchEnd={handleTouchEnd}
                                >
                                    <canvas
                                        ref={canvasRef}
                                        width={CANVAS_SIZE}
                                        height={CANVAS_SIZE}
                                        style={{ width: '100%', height: '100%', display: 'block' }}
                                    />
                                    
                                    <div className="position-absolute bottom-0 start-50 translate-middle-x mb-2 px-3 py-1 rounded-pill bg-dark bg-opacity-75 text-white small fw-semibold pointer-events-none d-flex align-items-center gap-1 shadow-sm" style={{ fontSize: '11px', pointerEvents: 'none' }}>
                                        <Move size={12} /> Geser untuk memposisikan foto
                                    </div>
                                </div>

                                {/* Shape Switcher */}
                                <div className="d-flex gap-2 mt-3">
                                    <button
                                        type="button"
                                        onClick={() => setCropShape('circle')}
                                        className={`btn btn-sm rounded-pill px-3 d-flex align-items-center gap-1 ${
                                            cropShape === 'circle' ? 'btn-success fw-bold shadow-sm' : 'btn-outline-secondary'
                                        }`}
                                    >
                                        <Circle size={14} /> Lingkaran (Avatar)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCropShape('square')}
                                        className={`btn btn-sm rounded-pill px-3 d-flex align-items-center gap-1 ${
                                            cropShape === 'square' ? 'btn-success fw-bold shadow-sm' : 'btn-outline-secondary'
                                        }`}
                                    >
                                        <Square size={14} /> Persegi
                                    </button>
                                </div>
                            </div>

                            {/* Controls & Live Previews */}
                            <div className="col-lg-5">
                                <div className="p-3 rounded-4" style={{ background: 'var(--bg-elevated)', border: '1px solid var(--border-color)' }}>
                                    <h6 className="fw-bold mb-3 d-flex align-items-center gap-1" style={{ color: 'var(--text-heading)' }}>
                                        <Sparkles size={16} className="text-warning" /> Pratinjau Tampilan
                                    </h6>
                                    
                                    {/* Preview Displays */}
                                    <div className="d-flex align-items-center justify-content-around py-3 mb-3 bg-body rounded-3 border" style={{ borderColor: 'var(--border-color)' }}>
                                        <div className="text-center">
                                            <div className="rounded-circle overflow-hidden shadow-sm mx-auto mb-1 border border-2 border-success" style={{ width: '76px', height: '76px' }}>
                                                <canvas ref={previewCircleRef} width={76} height={76} />
                                            </div>
                                            <small className="text-muted" style={{ fontSize: '11px' }}>Lingkaran</small>
                                        </div>
                                        <div className="text-center">
                                            <div className="rounded-3 overflow-hidden shadow-sm mx-auto mb-1 border border-2 border-primary" style={{ width: '76px', height: '76px' }}>
                                                <canvas ref={previewSquareRef} width={76} height={76} />
                                            </div>
                                            <small className="text-muted" style={{ fontSize: '11px' }}>Persegi</small>
                                        </div>
                                    </div>

                                    {/* Zoom Slider */}
                                    <div className="mb-3">
                                        <div className="d-flex justify-content-between align-items-center mb-1">
                                            <label className="form-label small fw-semibold mb-0">Skala Zoom</label>
                                            <span className="badge bg-success bg-opacity-10 text-success fw-bold">{Math.round(zoom * 100)}%</span>
                                        </div>
                                        <div className="d-flex align-items-center gap-2">
                                            <button 
                                                type="button" 
                                                className="btn btn-sm btn-outline-secondary rounded-circle p-1"
                                                onClick={() => setZoom((prev) => Math.max(1, +(prev - 0.2).toFixed(2)))}
                                                title="Zoom Out"
                                            >
                                                <ZoomOut size={14} />
                                            </button>
                                            <input
                                                type="range"
                                                className="form-range flex-grow-1"
                                                min="1"
                                                max="4"
                                                step="0.05"
                                                value={zoom}
                                                onChange={(e) => setZoom(parseFloat(e.target.value))}
                                            />
                                            <button 
                                                type="button" 
                                                className="btn btn-sm btn-outline-secondary rounded-circle p-1"
                                                onClick={() => setZoom((prev) => Math.min(4, +(prev + 0.2).toFixed(2)))}
                                                title="Zoom In"
                                            >
                                                <ZoomIn size={14} />
                                            </button>
                                        </div>
                                    </div>

                                    {/* Rotation and Reset Controls */}
                                    <div className="mb-2">
                                        <label className="form-label small fw-semibold mb-2">Putar & Atur Ulang</label>
                                        <div className="d-flex gap-2">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-secondary rounded-3 flex-fill d-flex align-items-center justify-content-center gap-1"
                                                onClick={handleRotateCcw}
                                                title="Putar 90° ke kiri"
                                            >
                                                <RotateCcw size={14} /> -90°
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-secondary rounded-3 flex-fill d-flex align-items-center justify-content-center gap-1"
                                                onClick={handleRotateCw}
                                                title="Putar 90° ke kanan"
                                            >
                                                <RotateCw size={14} /> +90°
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-danger rounded-3 flex-fill d-flex align-items-center justify-content-center gap-1"
                                                onClick={handleReset}
                                                title="Kembalikan ke posisi awal"
                                            >
                                                <RefreshCw size={14} /> Reset
                                            </button>
                                        </div>
                                    </div>

                                    {/* Info Resolution */}
                                    <div className="mt-3 pt-2 border-top small text-muted d-flex justify-content-between" style={{ fontSize: '11px', borderColor: 'var(--border-color)' }}>
                                        <span>Resolusi Asli:</span>
                                        <span className="fw-semibold">{imageDimensions.width} × {imageDimensions.height} px</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Modal Footer */}
                    <div className="modal-footer border-top px-4 py-3" style={{ borderColor: 'var(--border-color)' }}>
                        <button 
                            type="button" 
                            className="btn btn-outline-secondary rounded-pill px-4" 
                            onClick={onClose}
                        >
                            <X size={16} className="me-1" /> Batal
                        </button>
                        <button 
                            type="button" 
                            className="btn btn-agro rounded-pill px-4 fw-bold d-flex align-items-center gap-2 shadow"
                            onClick={handleApplyCrop}
                        >
                            <Check size={18} /> Terapkan & Simpan Crop
                        </button>
                    </div>

                </div>
            </div>
        </div>
    );
};

export default ImageCropperModal;
