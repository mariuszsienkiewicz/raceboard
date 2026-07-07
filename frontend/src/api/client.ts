import { getToken, removeToken } from "./auth";

const API_URL = import.meta.env.VITE_API_URL ?? '';

export async function apiFetch(path: string, options: RequestInit = {}) {
    const token = getToken();

    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        ...options.headers as Record<string, string>,
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_URL}${path}`, {
        ...options,
        headers,
    });

    if (response.status === 401) {
        removeToken();
        window.location.href = '/login';
    }

    return response;
}
