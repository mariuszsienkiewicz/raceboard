import { useState } from "react";
import { Link, Navigate } from "react-router-dom";
import { Button } from "@heroui/react";
import { Surface } from "@heroui/react/surface";
import { EnvelopeIcon, LockClosedIcon, EyeIcon, EyeSlashIcon } from "@heroicons/react/24/outline";
import { apiFetch } from "../api/client";
import { useAuth } from "../context/useAuth";

export default function LoginPage() {
    const { isAuthenticated, login } = useAuth();
    if (isAuthenticated) {
        return <Navigate to="/" replace />;
    }

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [showPassword, setShowPassword] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const isValid = email.length > 0 && password.length > 0;

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!isValid) return;

        setLoading(true);
        setError(null);

        try {
            const res = await apiFetch("/api/login", {
                method: "POST",
                body: JSON.stringify({ email, password }),
            });

            if (!res.ok) {
                setError("Invalid email or password.");
                return;
            }

            const data = await res.json();
            login(data.token);
        } catch {
            setError("Something went wrong. Please try again.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex justify-center px-4 py-8 md:py-16">
            <div className="w-full max-w-sm">
                <Surface variant="default" className="rounded-3xl p-8 shadow-sm">
                    <div className="flex flex-col gap-6">
                        <div className="flex flex-col gap-1">
                            <h1 className="text-xl font-bold text-foreground">Welcome back</h1>
                            <p className="text-sm text-muted">
                                Don't have an account?{" "}
                                <Link to="/register" className="font-medium text-primary hover:underline underline-offset-2">
                                    Sign up
                                </Link>
                            </p>
                        </div>

                        <form onSubmit={handleSubmit} noValidate className="flex flex-col gap-4">
                            {/* Email */}
                            <div className="flex flex-col gap-1.5">
                                <label htmlFor="email" className="text-xs font-semibold uppercase tracking-wide text-muted">
                                    Email
                                </label>
                                <div className="relative">
                                    <EnvelopeIcon className="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-muted pointer-events-none" />
                                    <input
                                        id="email"
                                        type="email"
                                        autoComplete="email"
                                        required
                                        value={email}
                                        onChange={(e) => setEmail(e.target.value)}
                                        placeholder="you@example.com"
                                        className="h-11 w-full rounded-xl border border-border bg-surface pl-10 pr-4 text-sm text-foreground placeholder:text-muted/60 outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/20"
                                    />
                                </div>
                            </div>

                            {/* Password */}
                            <div className="flex flex-col gap-1.5">
                                <div className="flex items-center justify-between">
                                    <label htmlFor="password" className="text-xs font-semibold uppercase tracking-wide text-muted">
                                        Password
                                    </label>
                                    <Link
                                        to="#"
                                        className="text-xs text-muted hover:text-foreground transition-colors underline underline-offset-2"
                                    >
                                        Forgot password?
                                    </Link>
                                </div>
                                <div className="relative">
                                    <LockClosedIcon className="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-muted pointer-events-none" />
                                    <input
                                        id="password"
                                        type={showPassword ? "text" : "password"}
                                        autoComplete="current-password"
                                        required
                                        value={password}
                                        onChange={(e) => setPassword(e.target.value)}
                                        placeholder="Your password"
                                        className="h-11 w-full rounded-xl border border-border bg-surface pl-10 pr-11 text-sm text-foreground placeholder:text-muted/60 outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/20"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword((v) => !v)}
                                        className="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted hover:text-foreground transition-colors"
                                        aria-label={showPassword ? "Hide password" : "Show password"}
                                    >
                                        {showPassword
                                            ? <EyeSlashIcon className="size-4" />
                                            : <EyeIcon className="size-4" />
                                        }
                                    </button>
                                </div>
                            </div>

                            {error && (
                                <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                                    {error}
                                </div>
                            )}

                            <Button
                                type="submit"
                                isDisabled={!isValid || loading}
                                className="mt-1 h-11 w-full rounded-xl font-semibold"
                            >
                                {loading ? "Logging in…" : "Log in"}
                            </Button>
                        </form>
                    </div>
                </Surface>
            </div>
        </div>
    );
}