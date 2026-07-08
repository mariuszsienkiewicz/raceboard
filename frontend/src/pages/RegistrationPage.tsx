import { useState } from "react";
import { Link } from "react-router-dom";
import { CheckCircle2, Eye, EyeOff, Footprints, Lock, Mail, User } from "lucide-react";
import { apiFetch } from "@/api/client";
import { Badge } from "@/components/ui/badge";
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

interface RegisterPayload {
    email: string;
    displayName: string;
    password: string;
}

async function registerUser(payload: RegisterPayload): Promise<void> {
    const res = await apiFetch("/api/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
    });

    if (!res.ok) {
        const data = await res.json();
        throw new Error(data.message || "Registration failed");
    }
}

const PERKS = [
    "Save races to your watchlist",
    "Leave reviews after running",
    "Get notified about new editions",
] as const;

export default function RegistrationPage() {
    const [email, setEmail] = useState("");
    const [displayName, setDisplayName] = useState("");
    const [password, setPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const [showPassword, setShowPassword] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);

    const passwordsMatch = confirmPassword === "" || password === confirmPassword;
    const isValid = email.length > 0 && displayName.length > 0 && password.length >= 8 && password === confirmPassword;

    const handleSubmit = async (event: React.FormEvent) => {
        event.preventDefault();
        if (!isValid) {
            return;
        }

        setLoading(true);
        setError(null);

        try {
            await registerUser({ email, displayName, password });
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
                <PageSeo title="Account created" noIndex />
                <div className="flex flex-col items-center gap-4 text-center">
                    <div className="flex size-16 items-center justify-center rounded-full bg-primary/10">
                        <CheckCircle2 className="size-9 text-primary" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <h2 className="text-xl font-bold text-foreground">Account created!</h2>
                        <p className="text-sm text-muted-foreground">
                            Welcome to Raceboard. You can now log in.
                        </p>
                    </div>
                    <Button size="sm" render={<Link to="/login" />} nativeButton={false}>
                        Go to login
                    </Button>
                </div>
            </div>
        );
    }

    return (
        <div className="mx-auto flex max-w-4xl flex-col gap-10 py-8 md:flex-row md:gap-16 md:py-16">
            <PageSeo
                title="Create account"
                description="Join Raceboard to save races, leave reviews and track upcoming editions."
                noIndex
            />
            <div className="flex flex-col gap-6 md:flex-1 md:justify-center">
                <div className="flex flex-col gap-3">
                    <Badge
                        variant="outline"
                        className="h-7 w-fit gap-2 border-border/80 bg-background/80 px-3.5 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        <Footprints className="size-3.5" strokeWidth={1.75} />
                        Free account
                    </Badge>
                    <h1 className="text-3xl leading-snug font-bold tracking-tight text-foreground">
                        Join the running community
                    </h1>
                    <p className="text-sm leading-relaxed text-muted-foreground">
                        Track races you want to run, leave reviews and stay up to date with new editions — all in
                        one place.
                    </p>
                </div>

                <ul className="flex flex-col gap-3">
                    {PERKS.map((perk) => (
                        <li key={perk} className="flex items-center gap-3 text-sm text-muted-foreground">
                            <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                <CheckCircle2 className="size-3.5 text-primary" />
                            </span>
                            {perk}
                        </li>
                    ))}
                </ul>
            </div>

            <div className="md:flex-1">
                <div className="rounded-3xl border border-border bg-card p-8 text-card-foreground shadow-sm">
                    <div className="flex flex-col gap-6">
                        <div className="flex flex-col gap-1">
                            <h2 className="text-xl font-bold text-foreground">Create account</h2>
                            <p className="text-sm text-muted-foreground">
                                Already have one?{" "}
                                <Link
                                    to="/login"
                                    className={cn(buttonVariants({ variant: "link", size: "sm" }), "h-auto p-0")}
                                >
                                    Log in
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
                                <Label
                                    htmlFor="display-name"
                                    className="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    Name
                                </Label>
                                <InputGroup className="h-11">
                                    <InputGroupAddon>
                                        <User />
                                    </InputGroupAddon>
                                    <InputGroupInput
                                        id="display-name"
                                        type="text"
                                        autoComplete="name"
                                        required
                                        value={displayName}
                                        onChange={(event) => setDisplayName(event.target.value)}
                                        placeholder="John Doe"
                                    />
                                </InputGroup>
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label
                                    htmlFor="password"
                                    className="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    Password
                                </Label>
                                <InputGroup className="h-11">
                                    <InputGroupAddon>
                                        <Lock />
                                    </InputGroupAddon>
                                    <InputGroupInput
                                        id="password"
                                        type={showPassword ? "text" : "password"}
                                        autoComplete="new-password"
                                        required
                                        value={password}
                                        onChange={(event) => setPassword(event.target.value)}
                                        placeholder="Min. 8 characters"
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
                                {password.length > 0 && password.length < 8 && (
                                    <p className="text-xs text-destructive">
                                        Password must be at least 8 characters.
                                    </p>
                                )}
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label
                                    htmlFor="confirm-password"
                                    className="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    Confirm password
                                </Label>
                                <InputGroup
                                    className={cn(
                                        "h-11",
                                        !passwordsMatch &&
                                            "has-[[data-slot=input-group-control]:focus-visible]:border-destructive has-[[data-slot=input-group-control]:focus-visible]:ring-destructive/20 border-destructive/40",
                                    )}
                                >
                                    <InputGroupAddon>
                                        <User />
                                    </InputGroupAddon>
                                    <InputGroupInput
                                        id="confirm-password"
                                        type={showPassword ? "text" : "password"}
                                        autoComplete="new-password"
                                        required
                                        value={confirmPassword}
                                        onChange={(event) => setConfirmPassword(event.target.value)}
                                        placeholder="Repeat password"
                                        aria-invalid={!passwordsMatch}
                                    />
                                </InputGroup>
                                {!passwordsMatch && (
                                    <p className="text-xs text-destructive">Passwords do not match.</p>
                                )}
                            </div>

                            {error && (
                                <div className="rounded-2xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                                    {error}
                                </div>
                            )}

                            <Button type="submit" disabled={!isValid || loading} className="mt-1 h-11 w-full">
                                {loading ? "Creating account…" : "Create account"}
                            </Button>
                        </form>

                        <p className="text-center text-xs leading-relaxed text-muted-foreground">
                            By signing up you agree to our{" "}
                            <Link
                                to="/terms"
                                className="underline underline-offset-2 transition-colors hover:text-foreground"
                            >
                                Terms of use
                            </Link>{" "}
                            and{" "}
                            <Link
                                to="/privacy"
                                className="underline underline-offset-2 transition-colors hover:text-foreground"
                            >
                                Privacy policy
                            </Link>
                            .
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
