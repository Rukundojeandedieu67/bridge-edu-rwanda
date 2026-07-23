import { createContext, useContext, useEffect, useMemo, useState } from 'react';
import apiClient from '../api/client';

const TOKEN_STORAGE_KEY = 'bridgeedu_api_token';
const USER_STORAGE_KEY = 'bridgeedu_user';

const AuthContext = createContext(null);

const readStoredUser = () => {
    const rawUser = localStorage.getItem(USER_STORAGE_KEY);
    return rawUser ? JSON.parse(rawUser) : null;
};

const persistAuthState = (token, user) => {
    localStorage.setItem(TOKEN_STORAGE_KEY, token);
    localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user));
};

const clearAuthState = () => {
    localStorage.removeItem(TOKEN_STORAGE_KEY);
    localStorage.removeItem(USER_STORAGE_KEY);
};

export function AuthProvider({ children }) {
    const [token, setToken] = useState(() => localStorage.getItem(TOKEN_STORAGE_KEY));
    const [user, setUser] = useState(() => readStoredUser());
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        setToken(localStorage.getItem(TOKEN_STORAGE_KEY));
        setUser(readStoredUser());
        setIsLoading(false);
    }, []);

    const login = async (credentials) => {
        const { data } = await apiClient.post('/auth/login', credentials);
        persistAuthState(data.token, data.user);
        setToken(data.token);
        setUser(data.user);
        return data;
    };

    const register = async (payload) => {
        const { data } = await apiClient.post('/auth/register', payload);
        persistAuthState(data.token, data.user);
        setToken(data.token);
        setUser(data.user);
        return data;
    };

    const logout = async () => {
        try {
            await apiClient.post('/auth/logout');
        } catch (error) {
            // Ignore backend errors during logout and clear client state anyway.
        }

        clearAuthState();
        setToken(null);
        setUser(null);
    };

    const value = useMemo(
        () => ({
            token,
            user,
            login,
            register,
            logout,
            isAuthenticated: Boolean(token && user),
            isLoading,
        }),
        [token, user, isLoading],
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }

    return context;
}
