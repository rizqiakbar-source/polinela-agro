import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import client from '../api/client';
import ProductCard from '../components/ProductCard';
import { Search, Filter, X, ArrowUpDown } from 'lucide-react';

const Katalog = () => {
    const [searchParams, setSearchParams] = useSearchParams();
    
    const [products, setProducts] = useState([]);
    const [units, setUnits] = useState([]);
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });

    const selectedUnit = searchParams.get('unit') || '';
    const selectedCategory = searchParams.get('kategori') || '';
    const searchQuery = searchParams.get('q') || '';
    const selectedSort = searchParams.get('sort') || 'terbaru';
    const currentPage = parseInt(searchParams.get('page') || '1', 10);

    // Fetch filters once
    useEffect(() => {
        const fetchFilters = async () => {
            try {
                const [unitsRes, catRes] = await Promise.all([
                    client.get('/units'),
                    client.get('/categories'),
                ]);
                if (unitsRes.data.status === 'success') setUnits(unitsRes.data.data || []);
                if (catRes.data.status === 'success') setCategories(catRes.data.data || []);
            } catch (err) {
                console.error('Failed to load filter options:', err);
            }
        };
        fetchFilters();
    }, []);

    // Fetch products whenever params change
    useEffect(() => {
        const fetchProducts = async () => {
            setLoading(true);
            try {
                const params = new URLSearchParams();
                if (selectedUnit) params.append('unit_id', selectedUnit);
                if (selectedCategory) params.append('category_id', selectedCategory);
                if (searchQuery) params.append('search', searchQuery);
                if (selectedSort) params.append('sort', selectedSort);
                params.append('page', currentPage);

                const res = await client.get(`/products?${params.toString()}`);
                if (res.data.status === 'success' || res.data.success) {
                    const pData = res.data.data;
                    const items = Array.isArray(pData) ? pData : (pData?.data || []);
                    setProducts(items);
                    setPagination({
                        current_page: pData?.current_page || 1,
                        last_page: pData?.last_page || 1,
                        total: pData?.total || items.length,
                    });
                }
            } catch (err) {
                console.error('Failed to fetch products:', err);
            } finally {
                setLoading(false);
            }
        };

        fetchProducts();
    }, [selectedUnit, selectedCategory, searchQuery, selectedSort, currentPage]);

    const updateFilter = (key, value) => {
        const nextParams = new URLSearchParams(searchParams);
        if (value) {
            nextParams.set(key, value);
        } else {
            nextParams.delete(key);
        }
        nextParams.set('page', '1'); // reset page
        setSearchParams(nextParams);
    };

    const clearFilters = () => {
        setSearchParams({});
    };

    const hasActiveFilters = selectedUnit || selectedCategory || searchQuery;

    return (
        <div className="py-4 bg-light min-vh-100">
            <div className="container">
                {/* Header Title */}
                <div className="mb-4">
                    <h2 className="fw-bold text-dark mb-1">Katalog Produk Perkebunan</h2>
                    <p className="text-muted small mb-0">Temukan produk pertanian dan olahan terbaik langsung dari unit perkebunan Polinela.</p>
                </div>

                <div className="row g-4">
                    {/* Sidebar Filters */}
                    <div className="col-lg-3">
                        <div className="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white">
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <h6 className="fw-bold mb-0 d-flex align-items-center gap-2">
                                    <Filter size={18} className="text-success" /> Filter Produk
                                </h6>
                                {hasActiveFilters && (
                                    <button onClick={clearFilters} className="btn btn-link btn-sm text-danger text-decoration-none p-0 small">
                                        Reset
                                    </button>
                                )}
                            </div>

                            {/* Search */}
                            <div className="mb-3">
                                <label className="form-label small fw-semibold">Pencarian</label>
                                <div className="input-group input-group-sm">
                                    <input
                                        type="text"
                                        className="form-control"
                                        placeholder="Cari nama produk..."
                                        value={searchQuery}
                                        onChange={(e) => updateFilter('q', e.target.value)}
                                    />
                                    {searchQuery && (
                                        <button className="btn btn-outline-secondary" onClick={() => updateFilter('q', '')}>
                                            <X size={14} />
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* Filter Unit Toko */}
                            <div className="mb-3">
                                <label className="form-label small fw-semibold">Unit Toko Perkebunan</label>
                                <div className="d-flex flex-column gap-1">
                                    <button
                                        type="button"
                                        className={`btn btn-sm text-start rounded-3 ${!selectedUnit ? 'btn-success text-white fw-bold' : 'btn-outline-light text-dark'}`}
                                        onClick={() => updateFilter('unit', '')}
                                    >
                                        Semua Unit Toko
                                    </button>
                                    {units.map((unit) => (
                                        <button
                                            key={unit.id}
                                            type="button"
                                            className={`btn btn-sm text-start rounded-3 d-flex justify-content-between align-items-center ${selectedUnit === String(unit.id) ? 'btn-success text-white fw-bold' : 'btn-outline-light text-dark'}`}
                                            onClick={() => updateFilter('unit', String(unit.id))}
                                        >
                                            <span>🏪 {unit.nama_unit}</span>
                                            <span className="badge bg-secondary bg-opacity-25 text-dark small">{unit.products_count || 0}</span>
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Filter Kategori */}
                            <div>
                                <label className="form-label small fw-semibold">Kategori</label>
                                <div className="d-flex flex-column gap-1">
                                    <button
                                        type="button"
                                        className={`btn btn-sm text-start rounded-3 ${!selectedCategory ? 'btn-success text-white fw-bold' : 'btn-outline-light text-dark'}`}
                                        onClick={() => updateFilter('kategori', '')}
                                    >
                                        Semua Kategori
                                    </button>
                                    {categories.map((cat) => (
                                        <button
                                            key={cat.id}
                                            type="button"
                                            className={`btn btn-sm text-start rounded-3 ${selectedCategory === String(cat.id) ? 'btn-success text-white fw-bold' : 'btn-outline-light text-dark'}`}
                                            onClick={() => updateFilter('kategori', String(cat.id))}
                                        >
                                            {cat.nama_kategori}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Product Grid Area */}
                    <div className="col-lg-9">
                        {/* Top Bar: Count & Sorting */}
                        <div className="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white d-flex flex-row justify-content-between align-items-center">
                            <span className="text-muted small">
                                Menampilkan <strong>{products.length}</strong> dari <strong>{pagination.total}</strong> produk
                            </span>

                            <div className="d-flex align-items-center gap-2">
                                <label className="small text-muted d-none d-sm-inline">Urutkan:</label>
                                <select
                                    className="form-select form-select-sm"
                                    style={{ width: '160px' }}
                                    value={selectedSort}
                                    onChange={(e) => updateFilter('sort', e.target.value)}
                                >
                                    <option value="terbaru">Terbaru</option>
                                    <option value="termurah">Harga Termurah</option>
                                    <option value="termahal">Harga Termahal</option>
                                    <option value="terlaris">Terlaris</option>
                                </select>
                            </div>
                        </div>

                        {/* Grid */}
                        {loading ? (
                            <div className="text-center py-5">
                                <div className="spinner-border text-success" role="status">
                                    <span className="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        ) : products.length === 0 ? (
                            <div className="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                                <div className="fs-1 mb-2">🔍</div>
                                <h5 className="fw-bold">Produk Tidak Ditemukan</h5>
                                <p className="text-muted small">Coba ubah kata kunci pencarian atau bersihkan filter yang aktif.</p>
                                <button className="btn btn-outline-success btn-sm rounded-pill mx-auto px-4" onClick={clearFilters}>
                                    Reset Semua Filter
                                </button>
                            </div>
                        ) : (
                            <div className="row g-4">
                                {products.map((product) => (
                                    <div key={product.id} className="col-xl-4 col-md-6 col-sm-6">
                                        <ProductCard product={product} />
                                    </div>
                                ))}
                            </div>
                        )}

                        {/* Pagination */}
                        {pagination.last_page > 1 && (
                            <nav className="mt-5 d-flex justify-content-center">
                                <ul className="pagination pagination-sm gap-1">
                                    <li className={`page-item ${pagination.current_page === 1 ? 'disabled' : ''}`}>
                                        <button className="page-link rounded-circle" onClick={() => updateFilter('page', pagination.current_page - 1)}>
                                            &laquo;
                                        </button>
                                    </li>
                                    {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map((page) => (
                                        <li key={page} className={`page-item ${pagination.current_page === page ? 'active' : ''}`}>
                                            <button className="page-link rounded-circle" onClick={() => updateFilter('page', page)}>
                                                {page}
                                            </button>
                                        </li>
                                    ))}
                                    <li className={`page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}`}>
                                        <button className="page-link rounded-circle" onClick={() => updateFilter('page', pagination.current_page + 1)}>
                                            &raquo;
                                        </button>
                                    </li>
                                </ul>
                            </nav>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Katalog;
