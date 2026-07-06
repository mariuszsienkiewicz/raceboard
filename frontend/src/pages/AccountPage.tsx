import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { Lock, Mail, User, UserCircle } from "lucide-react";
import { toast } from "sonner";
import { apiFetch } from "@/api/client";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import {
    Field,
    FieldDescription,
    FieldError,
    FieldGroup,
    FieldLabel,
    FieldSeparator,
} from "@/components/ui/field";
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from "@/components/ui/input-group";
import { Skeleton } from "@/components/ui/skeleton";
import { useAuth } from "@/context/useAuth";
import type { UserProfile } from "@/types/user";

async function fetchProfile(): Promise<UserProfile> {
    const res = await apiFetch("/api/me");

    if (!res.ok) {
        throw new Error("Failed to load profile");
    }

    return res.json();
}

async function updateDisplayName(displayName: string): Promise<void> {
    const res = await apiFetch("/api/me", {
        method: "PATCH",
        body: JSON.stringify({ displayName }),
    });

    if (res.status === 204) {
        return;
    }

    const data = await res.json().catch(() => ({}));
    throw new Error(data.error ?? "Failed to update profile");
}

function getInitials(displayName: string): string {
    const parts = displayName.trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) {
        return "?";
    }

    if (parts.length === 1) {
        return parts[0].charAt(0).toUpperCase();
    }

    return `${parts[0].charAt(0)}${parts[parts.length - 1].charAt(0)}`.toUpperCase();
}

function PageContainer({ children }: { children: React.ReactNode }) {
    return <div className="mx-auto w-full max-w-lg">{children}</div>;
}

function NotAuthenticated() {
    return (
        <PageContainer>
            <Card>
                <CardContent className="flex flex-col items-center gap-4 py-10 text-center">
                    <Avatar className="size-16">
                        <AvatarFallback>
                            <UserCircle className="size-8" />
                        </AvatarFallback>
                    </Avatar>
                    <div className="flex flex-col gap-1">
                        <p className="font-medium">Log in to manage your account</p>
                        <p className="text-sm text-muted-foreground">
                            Update your display name and keep your Raceboard profile up to date.
                        </p>
                    </div>
                    <Button size="sm" render={<Link to="/login" />} nativeButton={false}>
                        Log in
                    </Button>
                </CardContent>
            </Card>
        </PageContainer>
    );
}

function AccountSkeleton() {
    return (
        <PageContainer>
            <Card>
                <CardHeader className="border-b">
                    <div className="flex items-center gap-4">
                        <Skeleton className="size-14 rounded-full" />
                        <div className="flex flex-col gap-2">
                            <Skeleton className="h-5 w-32" />
                            <Skeleton className="h-4 w-44" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <FieldGroup>
                        <Skeleton className="h-20 w-full" />
                        <Skeleton className="h-20 w-full" />
                    </FieldGroup>
                </CardContent>
                <CardFooter className="border-t justify-end">
                    <Skeleton className="h-9 w-28" />
                </CardFooter>
            </Card>
        </PageContainer>
    );
}

function LoadError({ message }: { message: string }) {
    return (
        <PageContainer>
            <Card className="border-destructive/50">
                <CardContent>
                    <FieldError>{message}</FieldError>
                </CardContent>
            </Card>
        </PageContainer>
    );
}

export default function AccountPage() {
    const { isAuthenticated } = useAuth();
    const [profile, setProfile] = useState<UserProfile | null>(null);
    const [displayName, setDisplayName] = useState("");
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!isAuthenticated) {
            return;
        }

        let cancelled = false;

        const timeout = setTimeout(() => {
            setLoading(true);
            fetchProfile()
                .then((data) => {
                    if (!cancelled) {
                        setProfile(data);
                        setDisplayName(data.displayName);
                    }
                })
                .catch(() => {
                    if (!cancelled) {
                        setError("Failed to load profile. Please try again.");
                    }
                })
                .finally(() => {
                    if (!cancelled) {
                        setLoading(false);
                    }
                });
        }, 0);

        return () => {
            cancelled = true;
            clearTimeout(timeout);
        };
    }, [isAuthenticated]);

    const isDirty = profile !== null && displayName.trim() !== profile.displayName;
    const isValid = displayName.trim().length > 0;

    const handleSubmit = async (event: React.FormEvent) => {
        event.preventDefault();

        if (!isValid || !isDirty || saving) {
            return;
        }

        setSaving(true);
        setError(null);

        const trimmedName = displayName.trim();

        try {
            await updateDisplayName(trimmedName);
            setProfile((current) => (current ? { ...current, displayName: trimmedName } : current));
            setDisplayName(trimmedName);
            toast.success("Display name updated");
        } catch (err) {
            setError(err instanceof Error ? err.message : "Something went wrong. Please try again.");
        } finally {
            setSaving(false);
        }
    };

    const handleDiscard = () => {
        if (profile) {
            setDisplayName(profile.displayName);
            setError(null);
        }
    };

    if (!isAuthenticated) {
        return <NotAuthenticated />;
    }

    if (loading) {
        return <AccountSkeleton />;
    }

    if (!profile) {
        return <LoadError message={error ?? "Failed to load profile. Please try again."} />;
    }

    return (
        <PageContainer>
            <Card>
                <CardHeader className="border-b">
                    <div className="flex items-center gap-4">
                        <Avatar className="size-14">
                            <AvatarFallback className="text-lg font-medium">
                                {getInitials(displayName)}
                            </AvatarFallback>
                        </Avatar>
                        <div className="min-w-0">
                            <CardTitle className="truncate">{displayName}</CardTitle>
                            <CardDescription className="truncate">{profile.email}</CardDescription>
                        </div>
                    </div>
                </CardHeader>

                <form onSubmit={handleSubmit} noValidate className="contents">
                    <CardContent>
                        <FieldGroup>
                            <Field>
                                <FieldLabel htmlFor="email">Email address</FieldLabel>
                                <InputGroup>
                                    <InputGroupAddon>
                                        <Mail />
                                    </InputGroupAddon>
                                    <InputGroupInput
                                        id="email"
                                        type="email"
                                        value={profile.email}
                                        disabled
                                        tabIndex={-1}
                                    />
                                    <InputGroupAddon align="inline-end">
                                        <Lock />
                                    </InputGroupAddon>
                                </InputGroup>
                                <FieldDescription>Your login email cannot be changed.</FieldDescription>
                            </Field>

                            <FieldSeparator />

                            <Field>
                                <FieldLabel htmlFor="display-name">Display name</FieldLabel>
                                <InputGroup>
                                    <InputGroupAddon>
                                        <User />
                                    </InputGroupAddon>
                                    <InputGroupInput
                                        id="display-name"
                                        type="text"
                                        autoComplete="nickname"
                                        required
                                        value={displayName}
                                        onChange={(event) => setDisplayName(event.target.value)}
                                        placeholder="How others see you"
                                    />
                                </InputGroup>
                                <FieldDescription>Shown publicly on your reviews and activity.</FieldDescription>
                            </Field>

                            {error && <FieldError>{error}</FieldError>}
                        </FieldGroup>
                    </CardContent>

                    <CardFooter className="border-t justify-end gap-2">
                        {isDirty && (
                            <Button
                                type="button"
                                variant="ghost"
                                onClick={handleDiscard}
                                disabled={saving}
                            >
                                Discard
                            </Button>
                        )}
                        <Button type="submit" disabled={!isValid || !isDirty || saving}>
                            {saving ? "Saving…" : "Save changes"}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </PageContainer>
    );
}
