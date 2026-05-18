import { useState } from "react";
import { Link } from "react-router-dom";
import { Button } from "@heroui/react";
import { Surface } from "@heroui/react/surface";
import { EnvelopeIcon, LockClosedIcon, UserIcon, EyeIcon, EyeSlashIcon } from "@heroicons/react/24/outline";
import { CheckCircleIcon } from "@heroicons/react/24/solid";
import { apiFetch } from "../api/client";

interface RegisterPayload {
    email: string;
    password: string;
}

async function registerUser(_payload: RegisterPayload): Promise<void> {
    apiFetch("/api/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(_payload),
    }).then((res) => {
        if (!res.ok) {
            return res.json().then((data) => {
                throw new Error(data.message || "Registration failed");
            });
        }
    });
}

const PERKS = [
    "Save races to your watchlist",
    "Leave reviews after running",
    "Get notified about new editions",
];

export default function RegistrationPage() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const [showPassword, setShowPassword] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);

    const passwordsMatch = confirmPassword === "" || password === confirmPassword;
    const isValid = email.length > 0 && password.length >= 8 && password === confirmPassword;

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!isValid) return;

        setLoading(true);
        setError(null);

        try {
            await registerUser({ email, password });
            setSuccess(true);
        } catch (err) {
            setError(err instanceof Error ? err.message : "Something went wrong. Please try again.");
        } finally {
            setLoading(false);
        }
    };

    if (success) {
        return (
            <div className="flex min-h-[60vh] items-center justify-center px-4">
                <div className="flex flex-col items-center gap-4 text-center">
                    <div className="flex size-16 items-center justify-center rounded-full bg-primary/10">
                        <CheckCircleIcon className="size-9 text-primary" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <h2 className="text-xl font-bold text-foreground">Account created!</h2>
                        <p className="text-sm text-muted">Welcome to Raceboard. You can now log in.</p>
                    </div>
                    <Link to="/login">
                        <Button size="sm" className="mt-2 rounded-lg font-medium">
                            Go to login
                        </Button>
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="mx-auto flex max-w-4xl flex-col gap-10 py-8 md:flex-row md:gap-16 md:py-16">
            {/* Left — value prop */}
            <div className="flex flex-col gap-6 md:flex-1 md:justify-center">
                <div className="flex flex-col gap-3">
                    <div className="inline-flex w-fit items-center gap-2 rounded-full border border-border bg-surface px-3.5 py-1 text-xs font-medium uppercase tracking-wide text-muted">
                        🏃 Free account
                    </div>
                    <h1 className="text-3xl font-bold tracking-tight text-foreground leading-snug">
                        Join the running community
                    </h1>
                    <p className="text-sm text-muted leading-relaxed">
                        Track races you want to run, leave reviews and stay up to date with new editions - all in one place.
                    </p>
                </div>

                <ul className="flex flex-col gap-3">
                    {PERKS.map((perk) => (
                        <li key={perk} className="flex items-center gap-3 text-sm text-muted">
                            <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                <CheckCircleIcon className="size-3.5 text-primary" />
                            </span>
                            {perk}
                        </li>
                    ))}
                </ul>
            </div>

            {/* Right — form */}
            <div className="md:flex-1">
                <Surface variant="default" className="rounded-3xl p-8 shadow-sm">
                    <div className="flex flex-col gap-6">
                        <div className="flex flex-col gap-1">
                            <h2 className="text-xl font-bold text-foreground">Create account</h2>
                            <p className="text-sm text-muted">
                                Already have one?{" "}
                                <Link to="/login" className="font-medium text-primary hover:underline underline-offset-2">
                                    Log in
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
                                <label htmlFor="password" className="text-xs font-semibold uppercase tracking-wide text-muted">
                                    Password
                                </label>
                                <div className="relative">
                                    <LockClosedIcon className="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-muted pointer-events-none" />
                                    <input
                                        id="password"
                                        type={showPassword ? "text" : "password"}
                                        autoComplete="new-password"
                                        required
                                        value={password}
                                        onChange={(e) => setPassword(e.target.value)}
                                        placeholder="Min. 8 characters"
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
                                {password.length > 0 && password.length < 8 && (
                                    <p className="text-xs text-red-500">Password must be at least 8 characters.</p>
                                )}
                            </div>

                            {/* Confirm password */}
                            <div className="flex flex-col gap-1.5">
                                <label htmlFor="confirm-password" className="text-xs font-semibold uppercase tracking-wide text-muted">
                                    Confirm password
                                </label>
                                <div className="relative">
                                    <UserIcon className="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-muted pointer-events-none" />
                                    <input
                                        id="confirm-password"
                                        type={showPassword ? "text" : "password"}
                                        autoComplete="new-password"
                                        required
                                        value={confirmPassword}
                                        onChange={(e) => setConfirmPassword(e.target.value)}
                                        placeholder="Repeat password"
                                        className={`h-11 w-full rounded-xl border bg-surface pl-10 pr-4 text-sm text-foreground placeholder:text-muted/60 outline-none transition-colors focus:ring-2 ${
                                            !passwordsMatch
                                                ? "border-red-400 focus:border-red-400 focus:ring-red-200"
                                                : "border-border focus:border-primary focus:ring-primary/20"
                                        }`}
                                    />
                                </div>
                                {!passwordsMatch && (
                                    <p className="text-xs text-red-500">Passwords do not match.</p>
                                )}
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
                                {loading ? "Creating account…" : "Create account"}
                            </Button>
                        </form>

                        <p className="text-center text-xs text-muted leading-relaxed">
                            By signing up you agree to our{" "}
                            <Link to="/terms" className="hover:text-foreground underline underline-offset-2 transition-colors">
                                Terms of use
                            </Link>{" "}
                            and{" "}
                            <Link to="/privacy" className="hover:text-foreground underline underline-offset-2 transition-colors">
                                Privacy policy
                            </Link>
                            .
                        </p>
                    </div>
                </Surface>
            </div>
        </div>
    );
}
