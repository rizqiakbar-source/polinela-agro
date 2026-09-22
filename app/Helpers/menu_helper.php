<?php

if (!function_exists('is_active_menu')) {
    function is_active_menu(string|array $routes, string $activeClass = 'active'): string
    {
        $uri = service('uri');
        $currentPath = trim($uri->getPath(), '/');

        if (is_array($routes)) {
            foreach ($routes as $route) {
                $pattern = trim($route, '/');
                if ($pattern === '' && $currentPath === '') {
                    return $activeClass;
                }
                if ($pattern !== '' && (str_starts_with($currentPath, $pattern) || fnmatch($pattern, $currentPath))) {
                    return $activeClass;
                }
            }
            return '';
        }

        $pattern = trim($routes, '/');
        if ($pattern === '' && $currentPath === '') {
            return $activeClass;
        }
        if ($pattern !== '' && (str_starts_with($currentPath, $pattern) || fnmatch($pattern, $currentPath))) {
            return $activeClass;
        }

        return '';
    }
}
