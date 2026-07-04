import { useEffect, useState } from "react";
import { useParams, Link, useLocation } from "react-router-dom";
import { apiFetch } from "../api/client";
import type { RaceDetails } from "../types/race";
import { Chip, Separator, Skeleton } from "@heroui/react";
import { Surface } from "@heroui/react/surface";
import {
    ArrowLeftIcon,
    CalendarDaysIcon,
    MapPinIcon,
    MapIcon,
} from "@heroicons/react/24/outline";
import ReviewForm from "../components/review/ReviewForm";
import RaceReviews from "../components/review/RaceReviews";
import WatchlistButton from "../components/watchlist/WatchlistButton";
import EmptyState from "../components/EmptyState";

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
            <Skeleton className="h-4 w-24 rounded-lg" />
            <div className="flex flex-col gap-3">
                <Skeleton className="h-10 w-2/3 rounded-xl" />
                <Skeleton className="h-5 w-1/3 rounded-lg" />
            </div>
            <Skeleton className="h-px w-full" />
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 flex flex-col gap-3">
                    <Skeleton className="h-4 w-20 rounded-lg" />
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

    const handleAddReview = async (_rating: number, _comment: string): Promise<void> => {
        apiFetch(`/api/races/${id}/reviews`, {
            method: "POST",
            body: JSON.stringify({
                rating: _rating,
                comment: _comment,
            }),
        }).then((res) => {
            if (!res.ok) {
                throw new Error("Failed to submit review");
            }
            // refresh reviews - for now we just log success since reviews are placeholder data
            console.log("Review submitted successfully");
        }).catch((err) => {
            console.error("Error submitting review:", err);
            alert("Failed to submit review. Please try again.");
        });
    };

    useEffect(() => {
        if (!id) return;
        setLoading(true);
        apiFetch(`/api/races/${id}`)
            .then((res) => {
                if (!res.ok) throw new Error(res.statusText);
                return res.json() as Promise<RaceDetails>;
            })
            .then(setRaceDetails)
            .catch((err) => console.error("Error fetching race details:", err))
            .finally(() => setLoading(false));
    }, [id]);

    const today = new Date();
    const upcomingEditions = raceDetails?.editions
        .filter((e) => new Date(e.date) >= today)
        .sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime()) ?? [];
    const pastEditions = raceDetails?.editions
        .filter((e) => new Date(e.date) < today)
        .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()) ?? [];

    return (
        <div className="flex flex-col gap-8 py-4">
            <Link
                to={backTo}
                className="inline-flex items-center gap-1.5 text-sm text-muted hover:text-foreground transition-colors w-fit"
            >
                <ArrowLeftIcon className="size-4" />
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
                    {/* Hero */}
                    <div className="flex items-start justify-between gap-4">
                        <div className="flex flex-col gap-2">
                            <h1 className="text-3xl font-bold tracking-tight text-foreground leading-tight">
                                {raceDetails.name}
                            </h1>
                            <div className="flex items-center gap-1.5 text-sm text-muted">
                                <MapPinIcon className="size-4 shrink-0" />
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

                    {/* Editions + Map */}
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div className="lg:col-span-2 flex flex-col gap-5">
                            <h2 className="text-xs font-semibold uppercase tracking-widest text-muted">Editions</h2>

                            {upcomingEditions.length > 0 && (
                                <div className="flex flex-col gap-2">
                                    <span className="text-xs font-semibold uppercase tracking-wide text-primary">Upcoming</span>
                                    {upcomingEditions.map((edition) => (
                                        <Surface
                                            key={edition.date}
                                            variant="default"
                                            className="flex flex-col gap-2.5 rounded-2xl p-4 ring-1 ring-primary/25 bg-primary/5"
                                        >
                                            <div className="flex items-center gap-1.5 text-sm font-medium text-foreground">
                                                <CalendarDaysIcon className="size-4 text-primary" />
                                                {formatDate(edition.date)}
                                            </div>
                                            <div className="flex flex-wrap gap-1.5">
                                                {edition.distances.map((d) => (
                                                    <Chip key={d.id} size="sm" variant="soft">
                                                        {formatDistance(d.lengthInKm)}
                                                    </Chip>
                                                ))}
                                            </div>
                                        </Surface>
                                    ))}
                                </div>
                            )}

                            {pastEditions.length > 0 && (
                                <div className="flex flex-col gap-2">
                                    <span className="text-xs font-semibold uppercase tracking-wide text-muted">Past editions</span>
                                    {pastEditions.map((edition) => (
                                        <Surface
                                            key={edition.date}
                                            variant="default"
                                            className="flex flex-col gap-2.5 rounded-2xl p-4 opacity-60"
                                        >
                                            <div className="flex items-center gap-1.5 text-sm font-medium text-foreground">
                                                <CalendarDaysIcon className="size-4" />
                                                {formatDate(edition.date)}
                                            </div>
                                            <div className="flex flex-wrap gap-1.5">
                                                {edition.distances.map((d) => (
                                                    <Chip key={d.id} size="sm" variant="soft">
                                                        {formatDistance(d.lengthInKm)}
                                                    </Chip>
                                                ))}
                                            </div>
                                        </Surface>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Map placeholder */}
                        <div className="flex flex-col gap-5">
                            <h2 className="text-xs font-semibold uppercase tracking-widest text-muted">Location</h2>
                            <Surface
                                variant="default"
                                className="flex flex-col items-center justify-center gap-3 rounded-2xl p-8 min-h-[220px] border border-dashed border-border"
                            >
                                <MapIcon className="size-10 text-muted" style={{ opacity: 0.3 }} />
                                <p className="text-sm font-medium text-muted">Map coming soon</p>
                                <p className="text-xs text-center" style={{ opacity: 0.5 }}>
                                    {raceDetails.city}{raceDetails.voivodeship && `, ${raceDetails.voivodeship}`}
                                </p>
                            </Surface>
                        </div>
                    </div>

                    <Separator />

                    {/* Reviews */}
                    <div className="flex flex-col gap-5">
                        <div className="flex items-center justify-between">
                            <h2 className="text-xs font-semibold uppercase tracking-widest text-muted">Reviews</h2>
                            <span className="rounded-full bg-surface border border-border px-2.5 py-0.5 text-xs text-muted">
                                placeholder data
                            </span>
                        </div>

                        <ReviewForm onSubmit={handleAddReview} />
                        <RaceReviews raceId={raceDetails.id} />
                    </div>
                </>
            )}
        </div>
    );
}
