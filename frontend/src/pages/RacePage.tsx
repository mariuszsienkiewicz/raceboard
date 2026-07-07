import { useEffect, useState } from "react";
import { Link, useLocation, useParams } from "react-router-dom";
import { ArrowLeft, CalendarDays, MapPin } from "lucide-react";
import { apiFetch } from "@/api/client";
import EmptyState from "@/components/EmptyState";
import WatchlistButton from "@/components/watchlist/WatchlistButton";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { Skeleton } from "@/components/ui/skeleton";
import { cn } from "@/lib/utils";
import type { RaceDetails } from "@/types/race";
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
    return Number.isInteger(km) ? `${km} km` : `${km} km`;
}

function RacePageSkeleton() {
    return (
        <div className="flex flex-col gap-8">
            <Skeleton className="h-4 w-24" />
            <div className="flex flex-col gap-3">
                <Skeleton className="h-10 w-2/3 rounded-2xl" />
                <Skeleton className="h-5 w-1/3" />
            </div>
            <Skeleton className="h-px w-full" />
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="flex flex-col gap-3 lg:col-span-2">
                    <Skeleton className="h-4 w-20" />
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

    return (
        <div className="flex flex-col gap-8 py-4">
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
                    <div className="flex items-start justify-between gap-4">
                        <div className="flex flex-col gap-2">
                            <h1 className="text-3xl leading-tight font-bold tracking-tight text-foreground">
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

                    <Separator />

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div className="flex flex-col gap-5 lg:col-span-2">
                            <h2 className="text-xs font-semibold tracking-widest text-muted-foreground uppercase">
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
                                            <div className="flex items-center gap-1.5 text-sm font-medium text-foreground">
                                                <CalendarDays className="size-4 text-primary" />
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
                        </div>

                        <div className="flex flex-col gap-5">
                            <h2 className="text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                                Location
                            </h2>
                            <MapContainer
                                center={[raceDetails.latitude, raceDetails.longitude]}
                                zoom={13}
                                style={{ height: "220px", width: "100%" }}
                                className="rounded-2xl"
                            >
                                <TileLayer
                                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
                                    url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
                                />
                            </MapContainer>
                        </div>
                    </div>

                    <Separator />

                    <RaceReviews raceId={raceDetails.id} />
                </>
            )}
        </div>
    );
}
