import { useEffect, useState } from "react";
import { ThemeProviderContext, type Theme } from "@/components/ThemeProviderContext";

type ThemeProviderProps = {
    children: React.ReactNode;
    defaultTheme?: Theme;
    storageKey?: string;
};

export function ThemeProvider({
    children,
    defaultTheme = "system",
    storageKey = "vite-ui-theme",
    ...props
}: ThemeProviderProps) {
    const [theme, setTheme] = useState<Theme>(
        () => (localStorage.getItem(storageKey) as Theme) || defaultTheme,
    );

    useEffect(() => {
        const root = window.document.documentElement;
        const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

        const applyTheme = () => {
            root.classList.remove("light", "dark");

            if (theme === "system") {
                root.classList.add(mediaQuery.matches ? "dark" : "light");
                return;
            }

            root.classList.add(theme);
        };

        applyTheme();

        if (theme !== "system") {
            return;
        }

        mediaQuery.addEventListener("change", applyTheme);
        return () => mediaQuery.removeEventListener("change", applyTheme);
    }, [theme]);

    const value = {
        theme,
        setTheme: (nextTheme: Theme) => {
            localStorage.setItem(storageKey, nextTheme);
            setTheme(nextTheme);
        },
    };

    return (
        <ThemeProviderContext.Provider {...props} value={value}>
            {children}
        </ThemeProviderContext.Provider>
    );
}
