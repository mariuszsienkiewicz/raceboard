import { useRef, useState } from "react";
import { Avatar, AvatarFallback, Button } from "@heroui/react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { Bars3Icon, XMarkIcon, UserCircleIcon, ArrowRightStartOnRectangleIcon, Cog6ToothIcon } from "@heroicons/react/24/outline";
import { useAuth } from "../context/useAuth";

const NAV_LINKS = [
  { label: "Races", to: "/" },
  { label: "Watchlist", to: "/watchlist" },
  { label: "Reviews", to: "/reviews" },
  { label: "About", to: "/about" },
];

export default function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const { pathname } = useLocation();
  const navigate = useNavigate();
  const { isAuthenticated, logout } = useAuth();
  const userMenuRef = useRef<HTMLDivElement>(null);

  const handleLogout = () => {
    logout();
    setIsUserMenuOpen(false);
    navigate("/");
  };

  return (
    <nav className="sticky top-0 z-40 w-full border-b border-border bg-background/80 backdrop-blur-md">
      <div className="mx-auto flex h-16 max-w-5xl items-center justify-between px-6">
        {/* Brand */}
        <div className="flex items-center gap-8">
          <Link
            to="/"
            className="flex items-center gap-2 text-base font-bold tracking-tight text-foreground"
          >
            Raceboard
          </Link>

          {/* Desktop nav */}
          <ul className="hidden items-center gap-1 md:flex">
            {NAV_LINKS.map(({ label, to }) => {
              const active = pathname === to || (to !== "/" && pathname.startsWith(to));
              return (
                <li key={to}>
                  <Link
                    to={to}
                    className={`rounded-lg px-3 py-1.5 text-sm font-medium transition-colors ${active
                        ? "bg-primary/10 text-primary"
                        : "text-muted hover:bg-surface hover:text-foreground"
                      }`}
                  >
                    {label}
                  </Link>
                </li>
              );
            })}
          </ul>
        </div>

        {/* Desktop auth */}
        <div className="hidden items-center gap-3 md:flex">
          {isAuthenticated ? (
            <div className="relative" ref={userMenuRef}>
              <button
                onClick={() => setIsUserMenuOpen((v) => !v)}
                className="flex size-9 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary hover:bg-primary/20 transition-colors"
                aria-label="User menu"
                aria-expanded={isUserMenuOpen}
              >
                <Avatar>
                  <AvatarFallback>U</AvatarFallback>
                </Avatar>
              </button>
              {isUserMenuOpen && (
                <>
                  <div className="fixed inset-0 z-10" onClick={() => setIsUserMenuOpen(false)} />
                  <div className="absolute right-0 top-11 z-20 w-44 overflow-hidden rounded-xl border border-border bg-background shadow-lg">
                    <Link
                      to="/account"
                      onClick={() => setIsUserMenuOpen(false)}
                      className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-muted hover:bg-surface hover:text-foreground transition-colors"
                    >
                      <UserCircleIcon className="size-4" />
                      My account
                    </Link>
                    <Link
                      to="/watchlist"
                      onClick={() => setIsUserMenuOpen(false)}
                      className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-muted hover:bg-surface hover:text-foreground transition-colors"
                    >
                      <Cog6ToothIcon className="size-4" />
                      Watchlist
                    </Link>
                    <div className="my-1 border-t border-border" />
                    <button
                      onClick={handleLogout}
                      className="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors"
                    >
                      <ArrowRightStartOnRectangleIcon className="size-4" />
                      Log out
                    </button>
                  </div>
                </>
              )}
            </div>
          ) : (
            <>
              <Link
                to="/login"
                className="rounded-lg px-3 py-1.5 text-sm font-medium text-muted hover:bg-surface hover:text-foreground transition-colors"
              >
                Log in
              </Link>
              <Link to="/register">
                <Button size="sm" className="rounded-lg font-medium">
                  Sign up
                </Button>
              </Link>
            </>
          )}
        </div>

        {/* Mobile burger */}
        <button
          className="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-surface hover:text-foreground transition-colors md:hidden"
          onClick={() => setIsMenuOpen((v) => !v)}
          aria-label="Toggle menu"
          aria-expanded={isMenuOpen}
        >
          {isMenuOpen
            ? <XMarkIcon className="size-5" />
            : <Bars3Icon className="size-5" />
          }
        </button>
      </div>

      {/* Mobile menu */}
      {isMenuOpen && (
        <div className="border-t border-border bg-background md:hidden">
          <ul className="flex flex-col px-4 py-3">
            {NAV_LINKS.map(({ label, to }) => {
              const active = pathname === to || (to !== "/" && pathname.startsWith(to));
              return (
                <li key={to}>
                  <Link
                    to={to}
                    onClick={() => setIsMenuOpen(false)}
                    className={`block rounded-lg px-3 py-2 text-sm font-medium transition-colors ${active
                        ? "text-primary"
                        : "text-muted hover:text-foreground"
                      }`}
                  >
                    {label}
                  </Link>
                </li>
              );
            })}
            <li className="mt-3 flex flex-col gap-2 border-t border-border pt-3">
              {isAuthenticated ? (
                <>
                  <Link
                    to="/account"
                    onClick={() => setIsMenuOpen(false)}
                    className="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-muted hover:text-foreground transition-colors"
                  >
                    <UserCircleIcon className="size-4" />
                    My account
                  </Link>
                  <button
                    onClick={() => { handleLogout(); setIsMenuOpen(false); }}
                    className="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-red-500 hover:bg-red-50 transition-colors"
                  >
                    <ArrowRightStartOnRectangleIcon className="size-4" />
                    Log out
                  </button>
                </>
              ) : (
                <>
                  <Link
                    to="/login"
                    onClick={() => setIsMenuOpen(false)}
                    className="block rounded-lg px-3 py-2 text-sm font-medium text-muted hover:text-foreground transition-colors"
                  >
                    Log in
                  </Link>
                  <Link to="/register" onClick={() => setIsMenuOpen(false)}>
                    <Button size="sm" className="w-full rounded-lg font-medium">
                      Sign up
                    </Button>
                  </Link>
                </>
              )}
            </li>
          </ul>
        </div>
      )}
    </nav>
  );
}
