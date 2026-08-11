import { useContext } from "react";
import { ThemeProviderContext } from "@/components/ThemeProviderContext";

export function useTheme() {
    return useContext(ThemeProviderContext);
}
