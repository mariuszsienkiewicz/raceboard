import { useState } from "react";
import { Link, Navigate } from "react-router-dom";
import { Eye, EyeOff, Lock, Mail } from "lucide-react";
import { apiFetch } from "@/api/client";
import { Button, buttonVariants } from "@/components/ui/button";
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
} from "@/components/ui/input-group";
import { Label } from "@/components/ui/label";
import PageSeo from "@/components/PageSeo";
import { cn } from "@/lib/utils";
import { useAuth } from "@/context/useAuth";

export default function LoginPage() {
    const { isAuthenticated, login } = useAuth();
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [showPassword, setShowPassword] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const isValid = email.length > 0 && password.length > 0;

    const handleSubmit = async (event: React.FormEvent) => {
        event.preventDefault();
        if (!isValid) {
            return;
        }

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

    if (isAuthenticated) {
        return <Navigate to="/" replace />;
    }

    return (
        <div className="flex justify-center px-4 py-8 md:py-16">
            <PageSeo
                title="Log in"
                description="Sign in to your Raceboard account to manage your watchlist and reviews."
                noIndex
            />
            <div className="w-full max-w-sm">
                <div className="rounded-3xl border border-border bg-card p-8 text-card-foreground shadow-sm">
                    <div className="flex flex-col gap-6">
                        <div className="flex flex-col gap-1">
                            <h1 className="text-xl font-bold text-foreground">Welcome back</h1>
                            <p className="text-sm text-muted-foreground">
                                Don&apos;t have an account?{" "}
                                <Link
                                    to="/register"
                                    className={cn(buttonVariants({ variant: "link", size: "sm" }), "h-auto p-0")}
                                >
                                    Sign up
                                </Link>
                            </p>
                        </div>

                        <form onSubmit={handleSubmit} noValidate className="flex flex-col gap-4">
                            <div className="flex flex-col gap-1.5">
                                <Label
                                    htmlFor="email"
                                    className="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    Email
                                </Label>
                                <InputGroup className="h-11">
                                    <InputGroupAddon>
                                        <Mail />
                                    </InputGroupAddon>
                                    <InputGroupInput
                                        id="email"
                                        type="email"
                                        autoComplete="email"
                                        required
                                        value={email}
                                        onChange={(event) => setEmail(event.target.value)}
                                        placeholder="you@example.com"
                                    />
                                </InputGroup>
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <div className="flex items-center justify-between">
                                    <Label
                                        htmlFor="password"
                                        className="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                    >
                                        Password
                                    </Label>
                                    <Link
                                        to="#"
                                        className={cn(
                                            buttonVariants({ variant: "link", size: "sm" }),
                                            "h-auto p-0 text-xs",
                                        )}
                                    >
                                        Forgot password?
                                    </Link>
                                </div>
                                <InputGroup className="h-11">
                                    <InputGroupAddon>
                                        <Lock />
                                    </InputGroupAddon>
                                    <InputGroupInput
                                        id="password"
                                        type={showPassword ? "text" : "password"}
                                        autoComplete="current-password"
                                        required
                                        value={password}
                                        onChange={(event) => setPassword(event.target.value)}
                                        placeholder="Your password"
                                    />
                                    <InputGroupAddon align="inline-end">
                                        <InputGroupButton
                                            onClick={() => setShowPassword((value) => !value)}
                                            aria-label={showPassword ? "Hide password" : "Show password"}
                                        >
                                            {showPassword ? <EyeOff /> : <Eye />}
                                        </InputGroupButton>
                                    </InputGroupAddon>
                                </InputGroup>
                            </div>

                            {error && (
                                <div className="rounded-2xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                                    {error}
                                </div>
                            )}

                            <Button type="submit" disabled={!isValid || loading} className="mt-1 h-11 w-full">
                                {loading ? "Logging in…" : "Log in"}
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
