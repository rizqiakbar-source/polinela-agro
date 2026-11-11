import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api';
export const STORAGE_BASE_URL = import.meta.env.VITE_STORAGE_BASE_URL || '';

// In-memory cache store
const apiCache = new Map();
const DEFAULT_TTL = 3 * 60 * 1000; // 3 menit

export const clearApiCache = (pattern) => {
    if (!pattern) {
        apiCache.clear();
        return;
    }
    for (const key of apiCache.keys()) {
        if (key.includes(pattern)) {
            apiCache.delete(key);
        }
    }
};

const getCacheUserPrefix = () => {
    const token = localStorage.getItem('token');
    return token ? `user_${token.slice(-12)}` : 'guest';
};

const client = axios.create({
    baseURL: API_BASE_URL,
    timeout: 60000,
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

const isDynamicEndpoint = (url = '') => {
    const cleanUrl = url.toLowerCase();
    return (
        cleanUrl.includes('/cart') ||
        cleanUrl.includes('/orders') ||
        cleanUrl.includes('/payment') ||
        cleanUrl.includes('/admin') ||
        cleanUrl.includes('/user') ||
        cleanUrl.includes('/auth') ||
        cleanUrl.includes('/checkout') ||
        cleanUrl.includes('/notifications') ||
        cleanUrl.includes('/profil') ||
        cleanUrl.includes('/profile') ||
        cleanUrl.includes('/stock')
    );
};

// Request interceptor: attach token & check cache
client.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    // Auto-cache GET requests if not explicitly disabled and not a dynamic user endpoint
    if (config.method === 'get' && config.cache !== false && !isDynamicEndpoint(config.url)) {
        const cacheKey = `${getCacheUserPrefix()}:${config.url}?${JSON.stringify(config.params || {})}`;
        const cached = apiCache.get(cacheKey);

        if (cached && (Date.now() - cached.timestamp < (config.ttl || DEFAULT_TTL))) {
            // Serve immediately from memory cache!
            config.adapter = () => {
                return Promise.resolve({
                    data: cached.data,
                    status: 200,
                    statusText: 'OK (from cache)',
                    headers: cached.headers,
                    config: config,
                    request: {}
                });
            };
        }
    }

    // Invalidate cache on mutations (POST, PUT, PATCH, DELETE)
    if (['post', 'put', 'patch', 'delete'].includes(config.method)) {
        clearApiCache();
    }

    return config;
}, (error) => {
    return Promise.reject(error);
});

// Response interceptor: save to cache & handle 401
client.interceptors.response.use(
    (response) => {
        const config = response.config;
        if (config && config.method === 'get' && config.cache !== false && !isDynamicEndpoint(config.url) && response.status === 200) {
            const cacheKey = `${getCacheUserPrefix()}:${config.url}?${JSON.stringify(config.params || {})}`;
            apiCache.set(cacheKey, {
                data: response.data,
                headers: response.headers,
                timestamp: Date.now(),
            });
        }
        return response;
    },
    (error) => {
        if (error.response && error.response.status === 401) {
            clearApiCache();
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            if (!window.location.pathname.includes('/login')) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default client;
