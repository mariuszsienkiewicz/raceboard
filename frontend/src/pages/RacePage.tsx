import { useEffect, useState } from "react";
import { Link, useLocation, useParams } from "react-router-dom";
import {
    ArrowLeft,
    CalendarClock,
    CalendarDays,
    Flag,
    Globe,
    Layers,
    MapPin,
    Navigation,
    Route,
} from "lucide-react";
import { apiFetch } from "@/api/client";
import EmptyState from "@/components/EmptyState";
import PageSeo from "@/components/PageSeo";
import WatchlistButton from "@/components/watchlist/WatchlistButton";
import { Badge } from "@/components/ui/badge";
import { buttonVariants } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Skeleton } from "@/components/ui/skeleton";
import { cn } from "@/lib/utils";
import type { Editions, RaceDetails } from "@/types/race";
import { MapContainer, TileLayer } from "react-leaflet";
import RaceReviews from "@/components/review/RaceReviews";

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString("pl-PL", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}

function formatDistance(km: number): string {
    if (km === 42.195) return "Marathon";
    if (km === 21.0975) return "Half";
    return `${km} km`;
}

function daysUntil(dateStr: string): number {
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    const target = new Date(dateStr);
    target.setHours(0, 0, 0, 0);
    return Math.round((target.getTime() - now.getTime()) / 86_400_000);
}

function countdownLabel(dateStr: string): string {
    const diff = daysUntil(dateStr);
    if (diff === 0) return "Today";
    if (diff === 1) return "Tomorrow";
    if (diff < 7) return `In ${diff} days`;
    if (diff < 30) return `In ${Math.round(diff / 7)} weeks`;
    return `In ${Math.round(diff / 30)} months`;
}

function uniqueDistances(editions: Editions[]): number[] {
    const set = new Set<number>();
    editions.forEach((edition) => edition.distances.forEach((d) => set.add(d.lengthInKm)));
    return Array.from(set).sort((a, b) => a - b);
}

function buildAboutText(race: RaceDetails, distances: number[], nextDate: string | null): string {
    const place = [race.city, race.voivodeship].filter(Boolean).join(", ");
    const parts: string[] = [`${race.name} is a running event held in ${place || race.country}.`];

    if (distances.length === 1) {
        parts.push(`It features a single ${formatDistance(distances[0])} course.`);
    } else if (distances.length > 1) {
        parts.push(`It offers ${distances.length} distances, from ${formatDistance(distances[0])} to ${formatDistance(distances[distances.length - 1])}.`);
    }

    if (nextDate) {
        parts.push(`The next edition takes place on ${formatDate(nextDate)}.`);
    }

    return parts.join(" ");
}

function StatCard({
    icon: Icon,
    label,
    value,
    hint,
}: {
    icon: React.ComponentType<{ className?: string; strokeWidth?: number }>;
    label: string;
    value: string;
    hint?: string;
}) {
    return (
        <div className="flex flex-col gap-2 rounded-2xl border border-border bg-card p-4 ring-1 ring-foreground/5">
            <span className="flex items-center gap-1.5 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                <Icon className="size-3.5" strokeWidth={1.75} />
                {label}
            </span>
            <div className="flex flex-col">
                <span className="text-lg leading-tight font-semibold text-foreground">{value}</span>
                {hint && <span className="text-xs text-muted-foreground">{hint}</span>}
            </div>
        </div>
    );
}

function FactRow({ icon: Icon, label, value }: { icon: React.ComponentType<{ className?: string }>; label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-3 py-2.5 text-sm">
            <span className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                {label}
            </span>
            <span className="text-right font-medium text-foreground">{value}</span>
        </div>
    );
}

function buildRaceSeoDescription(race: RaceDetails, nextDate: string | null): string {
    const place = [race.city, race.voivodeship].filter(Boolean).join(", ");
    const location = place || race.country;

    if (nextDate) {
        const formattedDate = formatDate(nextDate);
        return `${race.name} in ${location}. Next edition on ${formattedDate}. Dates, distances and reviews on Raceboard.`;
    }

    return `${race.name} in ${location}. View dates, distances and reviews on Raceboard.`;
}

function RacePageSkeleton() {
    return (
        <div className="flex flex-col gap-8">
            <Skeleton className="h-4 w-24" />
            <div className="flex flex-col gap-3">
                <Skeleton className="h-10 w-2/3 rounded-2xl" />
                <Skeleton className="h-5 w-1/3" />
            </div>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Skeleton className="h-24 w-full rounded-2xl" />
                <Skeleton className="h-24 w-full rounded-2xl" />
                <Skeleton className="h-24 w-full rounded-2xl" />
            </div>
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="flex flex-col gap-3 lg:col-span-2">
                    <Skeleton className="h-24 w-full rounded-2xl" />
                    <Skeleton className="h-20 w-full rounded-2xl" />
                </div>
                <Skeleton className="h-48 w-full rounded-2xl" />
            </div>
        </div>
    );
}

export default function RacePage() {
    const { id } = useParams<{ id: string }>();
    const location = useLocation();
    const backTo = (location.state as { from?: string } | null)?.from ?? "/";

    const [raceDetails, setRaceDetails] = useState<RaceDetails | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) {
            return;
        }

        let cancelled = false;

        const timeout = setTimeout(async () => {
            setLoading(true);
            setRaceDetails(null);
            try {
                const res = await apiFetch(`/api/races/${id}`);
                if (!res.ok) {
                    throw new Error(res.statusText);
                }
                const data: RaceDetails = await res.json();
                if (!cancelled) {
                    setRaceDetails(data);
                }
            } catch (err) {
                if (!cancelled) {
                    console.error("Error fetching race details:", err);
                }
            } finally {
                if (!cancelled) {
                    setLoading(false);
                }
            }
        }, 0);

        return () => {
            cancelled = true;
            clearTimeout(timeout);
        };
    }, [id]);

    const today = new Date();
    const upcomingEditions =
        raceDetails?.editions
            .filter((e) => new Date(e.date) >= today)
            .sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime()) ?? [];
    const pastEditions =
        raceDetails?.editions
            .filter((e) => new Date(e.date) < today)
            .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()) ?? [];

    const distances = raceDetails ? uniqueDistances(raceDetails.editions) : [];
    const nextEdition = upcomingEditions[0] ?? null;
    const hasLocation =
        raceDetails?.latitude != null && raceDetails?.longitude != null;

    return (
        <div className="flex flex-col gap-8 py-4">
            {loading && (
                <PageSeo title="Race details" description="Running race details on Raceboard." />
            )}
            {!loading && !raceDetails && (
                <PageSeo title="Race not found" description="This race does not exist or has been removed." noIndex />
            )}
            {!loading && raceDetails && (
                <PageSeo
                    title={raceDetails.name}
                    description={buildRaceSeoDescription(raceDetails, nextEdition?.date ?? null)}
                />
            )}
            <Link
                to={backTo}
                className="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft className="size-4" />
                Back to search
            </Link>

            {loading ? (
                <RacePageSkeleton />
            ) : !raceDetails ? (
                <EmptyState
                    icon="notFound"
                    title="Race not found"
                    description="This race doesn't exist or has been removed."
                />
            ) : (
                <>
                    <section className="relative overflow-hidden rounded-4xl border border-border bg-card p-6 ring-1 ring-foreground/5 sm:p-8">
                        <div
                            aria-hidden
                            className="pointer-events-none absolute -top-16 -right-10 size-56 rounded-full bg-primary/5 blur-3xl"
                        />
                        <div className="relative flex flex-col gap-4">
                            <div className="flex flex-wrap items-center gap-2">
                                {raceDetails.voivodeship && (
                                    <Badge variant="secondary" className="gap-1">
                                        <MapPin className="size-3" />
                                        {raceDetails.voivodeship}
                                    </Badge>
                                )}
                                {nextEdition ? (
                                    <Badge className="gap-1">
                                        <CalendarClock className="size-3" />
                                        {countdownLabel(nextEdition.date)}
                                    </Badge>
                                ) : (
                                    <Badge variant="outline" className="gap-1 text-muted-foreground">
                                        No upcoming dates
                                    </Badge>
                                )}
                            </div>

                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-2">
                                    <h1 className="text-3xl leading-tight font-bold tracking-tight text-foreground sm:text-4xl">
                                        {raceDetails.name}
                                    </h1>
                                    <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                                        <MapPin className="size-4 shrink-0" />
                                        <span>
                                            {raceDetails.city}
                                            {raceDetails.voivodeship && (
                                                <span className="opacity-70">, {raceDetails.voivodeship}</span>
                                            )}
                                            {raceDetails.country && (
                                                <span className="opacity-70"> · {raceDetails.country}</span>
                                            )}
                                        </span>
                                    </div>
                                </div>
                                <WatchlistButton raceId={id || ""} />
                            </div>

                            <p className="max-w-2xl text-sm leading-relaxed text-muted-foreground">
                                {buildAboutText(raceDetails, distances, nextEdition?.date ?? null)}
                            </p>
                        </div>
                    </section>

                    <section className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <StatCard
                            icon={CalendarClock}
                            label="Next race"
                            value={nextEdition ? formatDate(nextEdition.date) : "To be announced"}
                            hint={nextEdition ? countdownLabel(nextEdition.date) : "No upcoming edition"}
                        />
                        <StatCard
                            icon={CalendarDays}
                            label="Editions"
                            value={String(raceDetails.editions.length)}
                            hint={`${pastEditions.length} in the past · ${upcomingEditions.length} upcoming`}
                        />
                        <StatCard
                            icon={Route}
                            label="Distances"
                            value={distances.length > 0 ? String(distances.length) : "TBA"}
                            hint={
                                distances.length > 0
                                    ? `${formatDistance(distances[0])} to ${formatDistance(distances[distances.length - 1])}`
                                    : "Not announced yet"
                            }
                        />
                    </section>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div className="flex flex-col gap-8 lg:col-span-2">
                            {distances.length > 0 && (
                                <section className="flex flex-col gap-3">
                                    <h2 className="flex items-center gap-2 text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                                        <Route className="size-3.5" />
                                        Distances offered
                                    </h2>
                                    <div className="flex flex-wrap gap-2">
                                        {distances.map((km) => (
                                            <Badge key={km} variant="outline" className="h-8 gap-1.5 px-3 text-sm">
                                                <Flag className="size-3.5 text-primary" />
                                                {formatDistance(km)}
                                            </Badge>
                                        ))}
                                    </div>
                                </section>
                            )}

                            <section className="flex flex-col gap-5">
                                <h2 className="flex items-center gap-2 text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                                    <CalendarDays className="size-3.5" />
                                    Editions
                                </h2>

                                {upcomingEditions.length > 0 && (
                                    <div className="flex flex-col gap-2">
                                        <span className="text-xs font-semibold tracking-wide text-primary uppercase">
                                            Upcoming
                                        </span>
                                        {upcomingEditions.map((edition) => (
                                            <article
                                                key={edition.date}
                                                className={cn(
                                                    "flex flex-col gap-2.5 rounded-2xl border border-primary/25 bg-primary/5 p-4",
                                                    "ring-1 ring-primary/25",
                                                )}
                                            >
                                                <div className="flex items-center justify-between gap-2">
                                                    <div className="flex items-center gap-1.5 text-sm font-medium text-foreground">
                                                        <CalendarDays className="size-4 text-primary" />
                                                        {formatDate(edition.date)}
                                                    </div>
                                                    <span className="text-xs font-medium text-primary">
                                                        {countdownLabel(edition.date)}
                                                    </span>
                                                </div>
                                                <div className="flex flex-wrap gap-1.5">
                                                    {edition.distances.map((d) => (
                                                        <Badge key={d.id} variant="secondary">
                                                            {formatDistance(d.lengthInKm)}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </article>
                                        ))}
                                    </div>
                                )}

                                {pastEditions.length > 0 && (
                                    <div className="flex flex-col gap-2">
                                        <span className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                            Past editions
                                        </span>
                                        {pastEditions.map((edition) => (
                                            <article
                                                key={edition.date}
                                                className="flex flex-col gap-2.5 rounded-2xl border border-border bg-card p-4 opacity-60"
                                            >
                                                <div className="flex items-center gap-1.5 text-sm font-medium text-foreground">
                                                    <CalendarDays className="size-4" />
                                                    {formatDate(edition.date)}
                                                </div>
                                                <div className="flex flex-wrap gap-1.5">
                                                    {edition.distances.map((d) => (
                                                        <Badge key={d.id} variant="secondary">
                                                            {formatDistance(d.lengthInKm)}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </article>
                                        ))}
                                    </div>
                                )}
                            </section>
                        </div>

                        <div className="flex flex-col gap-5">
                            <section className="flex flex-col gap-3">
                                <h2 className="flex items-center gap-2 text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                                    <MapPin className="size-3.5" />
                                    Location
                                </h2>
                                {hasLocation ? (
                                    <div className="flex flex-col gap-2">
                                        <MapContainer
                                            center={[raceDetails.latitude as number, raceDetails.longitude as number]}
                                            zoom={13}
                                            style={{ height: "220px", width: "100%" }}
                                            className="rounded-2xl"
                                        >
                                            <TileLayer
                                                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
                                                url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
                                            />
                                        </MapContainer>
                                        <a
                                            href={`https://www.google.com/maps/search/?api=1&query=${raceDetails.latitude},${raceDetails.longitude}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className={cn(
                                                buttonVariants({ variant: "outline", size: "sm" }),
                                                "w-full",
                                            )}
                                        >
                                            <Navigation className="size-4" />
                                            Open in maps
                                        </a>
                                    </div>
                                ) : (
                                    <div className="flex h-[220px] w-full flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-border bg-muted/30 text-center text-sm text-muted-foreground">
                                        <MapPin className="size-5 opacity-60" />
                                        <span>Location not available yet</span>
                                    </div>
                                )}
                            </section>

                            <section className="flex flex-col gap-1 rounded-2xl border border-border bg-card p-4 ring-1 ring-foreground/5">
                                <h2 className="mb-1 flex items-center gap-2 text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                                    <Layers className="size-3.5" />
                                    At a glance
                                </h2>
                                <FactRow icon={MapPin} label="City" value={raceDetails.city || "Unknown"} />
                                {raceDetails.voivodeship && (
                                    <>
                                        <Separator />
                                        <FactRow icon={Flag} label="Region" value={raceDetails.voivodeship} />
                                    </>
                                )}
                                {raceDetails.country && (
                                    <>
                                        <Separator />
                                        <FactRow icon={Globe} label="Country" value={raceDetails.country} />
                                    </>
                                )}
                                <Separator />
                                <FactRow
                                    icon={Route}
                                    label="Distances"
                                    value={distances.length > 0 ? String(distances.length) : "To be announced"}
                                />
                                <Separator />
                                <FactRow
                                    icon={CalendarDays}
                                    label="Editions"
                                    value={String(raceDetails.editions.length)}
                                />
                            </section>
                        </div>
                    </div>

                    <Separator />

                    <RaceReviews raceId={raceDetails.id} />
                </>
            )}
        </div>
    );
}
