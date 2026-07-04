import { Check, Monitor, Moon, Sun } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { cn } from "@/lib/utils";
import { useTheme } from "./ThemeProvider";

type ThemeOption = "light" | "dark" | "system";

const THEME_OPTIONS: { value: ThemeOption; label: string; icon: typeof Sun }[] = [
    { value: "light", label: "Light", icon: Sun },
    { value: "dark", label: "Dark", icon: Moon },
    { value: "system", label: "System", icon: Monitor },
];

interface ThemeModeToggleProps {
    className?: string;
}

export function ThemeModeToggle({ className }: ThemeModeToggleProps) {
    const { theme, setTheme } = useTheme();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger
                render={
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        className={cn(
                            "relative shrink-0 text-muted-foreground hover:text-foreground",
                            className,
                        )}
                        aria-label="Toggle theme"
                    />
                }
            >
                <Sun className="size-4 scale-100 rotate-0 transition-all dark:scale-0 dark:-rotate-90" />
                <Moon className="absolute size-4 scale-0 rotate-90 transition-all dark:scale-100 dark:rotate-0" />
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-36">
                {THEME_OPTIONS.map(({ value, label, icon: Icon }) => (
                    <DropdownMenuItem key={value} onClick={() => setTheme(value)}>
                        <Icon />
                        {label}
                        {theme === value && <Check className="ml-auto size-4 opacity-70" />}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
