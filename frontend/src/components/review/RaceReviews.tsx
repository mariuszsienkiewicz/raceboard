import { useEffect, useState } from "react";
import { Star } from "lucide-react";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { cn } from "@/lib/utils";
import { apiFetch } from "@/api/client";
import type { Review } from "@/types/review";

interface RaceReviewsProps {
    raceId: string;
}

function StarRating({ rating }: { rating: number }) {
    return (
        <div className="flex items-center gap-0.5">
            {Array.from({ length: 5 }).map((_, index) => (
                <Star
                    key={index}
                    className={cn(
                        "size-4",
                        index < rating
                            ? "fill-amber-400 text-amber-400"
                            : "text-muted-foreground",
                    )}
                />
            ))}
        </div>
    );
}

export default function RaceReviews({ raceId }: RaceReviewsProps) {
    const [reviews, setReviews] = useState<Review[]>([]);

    useEffect(() => {
        let cancelled = false;

        apiFetch(`/api/races/${raceId}/reviews`)
            .then((res) => res.json())
            .then((data: Review[]) => {
                if (!cancelled) {
                    setReviews(data);
                }
            })
            .catch((err) => {
                if (!cancelled) {
                    console.error("Failed to fetch reviews:", err);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [raceId]);

    if (reviews.length === 0) {
        return (
            <p className="text-sm text-muted-foreground">
                No reviews yet. Be the first to share your experience!
            </p>
        );
    }

    return (
        <div className="flex flex-col gap-4">
            {reviews.map((review) => (
                <article
                    key={review.id}
                    className="flex flex-col gap-3 rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-sm"
                >
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <Avatar size="sm">
                                <AvatarFallback>{review.displayName.charAt(0)}</AvatarFallback>
                            </Avatar>
                            <div>
                                <p className="text-sm font-medium text-foreground">{review.displayName}</p>
                                <p className="text-xs text-muted-foreground">{review.createdAt}</p>
                            </div>
                        </div>
                        <StarRating rating={review.rating} />
                    </div>
                    <p className="text-sm leading-relaxed text-muted-foreground">{review.comment}</p>
                </article>
            ))}
        </div>
    );
}
