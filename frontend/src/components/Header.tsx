import { useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { LogOut, Menu, UserCircle } from "lucide-react";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button, buttonVariants } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Separator } from "@/components/ui/separator";
import { Sheet, SheetContent, SheetHeader, SheetTitle } from "@/components/ui/sheet";
import { ThemeModeToggle } from "@/components/ThemeModeToggle";
import { cn } from "@/lib/utils";
import { useAuth } from "@/context/useAuth";

const NAV_LINKS = [
    { label: "Races", to: "/" },
    { label: "Watchlist", to: "/watchlist" },
    { label: "Reviews", to: "/reviews" },
    { label: "About", to: "/about" },
] as const;

function isNavActive(pathname: string, to: string): boolean {
    return pathname === to || (to !== "/" && pathname.startsWith(to));
}

function navLinkClassName(active: boolean): string {
    return cn(
        "rounded-2xl px-3 py-1.5 text-sm font-medium transition-colors",
        active
            ? "bg-primary/10 text-primary"
            : "text-muted-foreground hover:bg-muted hover:text-foreground",
    );
}

export default function Header() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const { pathname } = useLocation();
    const navigate = useNavigate();
    const { isAuthenticated, logout } = useAuth();

    function handleLogout() {
        logout();
        setIsMenuOpen(false);
        navigate("/");
    }

    return (
        <nav className="sticky top-0 z-40 w-full border-b border-border bg-background/80 backdrop-blur-md">
            <div className="mx-auto flex h-16 max-w-5xl items-center justify-between px-6">
                <div className="flex items-center gap-8">
                    <Link
                        to="/"
                        className="text-base font-bold tracking-tight text-foreground transition-opacity hover:opacity-80"
                    >
                        Raceboard
                    </Link>

                    <ul className="hidden items-center gap-1 md:flex">
                        {NAV_LINKS.map(({ label, to }) => (
                            <li key={to}>
                                <Link to={to} className={navLinkClassName(isNavActive(pathname, to))}>
                                    {label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>

                <div className="hidden items-center gap-2 md:flex">
                    <ThemeModeToggle />
                    {isAuthenticated ? (
                        <DropdownMenu>
                            <DropdownMenuTrigger
                                render={
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        className="rounded-full"
                                        aria-label="User menu"
                                    />
                                }
                            >
                                <Avatar size="sm">
                                    <AvatarFallback className="bg-primary/10 font-semibold text-primary">
                                        U
                                    </AvatarFallback>
                                </Avatar>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-44">
                                <DropdownMenuItem render={<Link to="/account" />} nativeButton={false}>
                                    <UserCircle />
                                    My account
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem variant="destructive" onClick={handleLogout}>
                                    <LogOut />
                                    Log out
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    ) : (
                        <>
                            <Link
                                to="/login"
                                className={cn(buttonVariants({ variant: "ghost", size: "sm" }))}
                            >
                                Log in
                            </Link>
                            <Button size="sm" render={<Link to="/register" />} nativeButton={false}>
                                Sign up
                            </Button>
                        </>
                    )}
                </div>

                <div className="flex items-center gap-1 md:hidden">
                    <ThemeModeToggle />
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Open menu"
                        onClick={() => setIsMenuOpen(true)}
                    >
                        <Menu className="size-5" />
                    </Button>
                </div>
            </div>

            <Sheet open={isMenuOpen} onOpenChange={setIsMenuOpen}>
                <SheetContent side="right" className="w-full sm:max-w-xs">
                    <SheetHeader className="pb-2 text-left">
                        <SheetTitle>Menu</SheetTitle>
                    </SheetHeader>

                    <nav className="flex flex-col gap-1 px-2">
                        {NAV_LINKS.map(({ label, to }) => (
                            <Link
                                key={to}
                                to={to}
                                onClick={() => setIsMenuOpen(false)}
                                className={cn(
                                    "rounded-2xl px-3 py-2.5 text-sm font-medium transition-colors",
                                    isNavActive(pathname, to)
                                        ? "bg-primary/10 text-primary"
                                        : "text-muted-foreground hover:bg-muted hover:text-foreground",
                                )}
                            >
                                {label}
                            </Link>
                        ))}
                    </nav>

                    <Separator className="my-4" />

                    <div className="flex flex-col gap-2 px-2">
                        {isAuthenticated ? (
                            <>
                                <Link
                                    to="/account"
                                    onClick={() => setIsMenuOpen(false)}
                                    className="flex items-center gap-2.5 rounded-2xl px-3 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                >
                                    <UserCircle className="size-4" />
                                    My account
                                </Link>
                                <Button
                                    variant="destructive"
                                    className="justify-start"
                                    onClick={handleLogout}
                                >
                                    <LogOut />
                                    Log out
                                </Button>
                            </>
                        ) : (
                            <>
                                <Link
                                    to="/login"
                                    onClick={() => setIsMenuOpen(false)}
                                    className={cn(
                                        buttonVariants({ variant: "ghost", size: "sm" }),
                                        "justify-start",
                                    )}
                                >
                                    Log in
                                </Link>
                                <Button
                                    size="sm"
                                    className="w-full"
                                    render={<Link to="/register" onClick={() => setIsMenuOpen(false)} />}
                                    nativeButton={false}
                                >
                                    Sign up
                                </Button>
                            </>
                        )}
                    </div>
                </SheetContent>
            </Sheet>
        </nav>
    );
}
