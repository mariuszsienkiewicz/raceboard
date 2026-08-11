import { useState } from "react";
import { getToken, removeToken, saveToken } from "../api/auth";
import { AuthContext } from "./auth-context";

export function AuthProvider({ children }: { children: React.ReactNode }) {
    const [isAuthenticated, setIsAuthenticated] = useState(!!getToken());

    const login = (token: string) => {
        saveToken(token);
        setIsAuthenticated(true);
    };

    const logout = () => {
        removeToken();
        setIsAuthenticated(false);
    };

    return (
        <AuthContext.Provider value={{ isAuthenticated, login, logout }}>
            {children}
        </AuthContext.Provider>
    );
}
